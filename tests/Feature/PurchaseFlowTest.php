<?php

use App\Livewire\Purchases\ManagePurchaseRequests;
use App\Livewire\Purchases\ManageSupplierQuotations;
use App\Livewire\Purchases\SelectWinningQuotation;
use App\Models\Company;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\PermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->company = Company::factory()->create();
    $this->user = User::factory()->create();
    $this->role = Role::findByName('Super Admin', 'web');

    $this->user->companies()->attach($this->company, [
        'role_id' => $this->role->id,
        'active' => true,
        'default_company' => true,
    ]);

    setPermissionsTeamId($this->company->id);
    $this->user->assignRole($this->role);

    $this->actingAs($this->user);
    session([CompanyContext::SESSION_KEY => $this->company->id]);
    setPermissionsTeamId($this->company->id);

    $this->project = Project::factory()->create([
        'company_id' => $this->company->id,
        'responsible_user_id' => $this->user->id,
    ]);

    $this->supplierA = Supplier::factory()->create([
        'company_id' => $this->company->id,
        'business_name' => 'Proveedor A',
    ]);

    $this->supplierB = Supplier::factory()->create([
        'company_id' => $this->company->id,
        'business_name' => 'Proveedor B',
    ]);
});

test('purchase requests can be created with scoped items', function () {
    Livewire::test(ManagePurchaseRequests::class)
        ->call('openCreateModal')
        ->set('code', 'SC-001')
        ->set('work_project_id', $this->project->id)
        ->set('requested_by', $this->user->id)
        ->set('priority', 'alta')
        ->set('request_date', now()->toDateString())
        ->set('description', 'Compra de materiales electricos')
        ->set('items', [
            [
                'product_or_service' => 'Cable THW',
                'unit' => 'rollo',
                'quantity' => '5',
                'technical_specification' => 'Calibre 10',
                'observation' => 'Uso en tablero general',
            ],
        ])
        ->call('savePurchaseRequest')
        ->assertHasNoErrors();

    $purchaseRequest = PurchaseRequest::query()->where('code', 'SC-001')->firstOrFail();

    $this->assertDatabaseHas('purchase_requests', [
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'code' => 'SC-001',
    ]);

    expect($purchaseRequest->items()->count())->toBe(1);
});

test('supplier quotations can be created and totals are calculated', function () {
    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'requested_by' => $this->user->id,
        'code' => 'SC-002',
        'priority' => 'media',
        'request_date' => now()->toDateString(),
        'description' => 'Compra de tuberias',
        'status' => 'solicitada',
    ]);

    $purchaseRequest->items()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'product_or_service' => 'Tubo PVC',
        'unit' => 'und',
        'quantity' => 10,
        'technical_specification' => '4 pulgadas',
    ]);

    Livewire::test(ManageSupplierQuotations::class, ['purchaseRequest' => $purchaseRequest])
        ->call('openCreateModal')
        ->set('supplier_id', $this->supplierA->id)
        ->set('code', 'COT-001')
        ->set('currency', 'PEN')
        ->set('tax', '180')
        ->set('delivery_time', '3')
        ->set('payment_conditions', 'Contado')
        ->set('warranty', '12 meses')
        ->set('items', [
            [
                'product_or_service' => 'Tubo PVC',
                'unit' => 'und',
                'quantity' => '10',
                'unit_price' => '50',
            ],
        ])
        ->call('saveQuotation')
        ->assertHasNoErrors();

    $quotation = SupplierQuotation::query()->where('code', 'COT-001')->firstOrFail();

    expect((float) $quotation->subtotal)->toBe(500.0);
    expect((float) $quotation->total)->toBe(680.0);
    expect($purchaseRequest->fresh()->status)->toBe('cotizada');
});

test('winner selection and purchase order generation are persisted', function () {
    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'requested_by' => $this->user->id,
        'code' => 'SC-003',
        'priority' => 'alta',
        'request_date' => now()->toDateString(),
        'description' => 'Compra de equipos',
        'status' => 'cotizada',
    ]);

    $quotationA = SupplierQuotation::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'purchase_request_id' => $purchaseRequest->id,
        'supplier_id' => $this->supplierA->id,
        'code' => 'COT-A',
        'quotation_date' => now()->toDateString(),
        'currency' => 'PEN',
        'subtotal' => 1000,
        'tax' => 180,
        'total' => 1180,
        'delivery_time' => 5,
    ]);

    $quotationB = SupplierQuotation::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'purchase_request_id' => $purchaseRequest->id,
        'supplier_id' => $this->supplierB->id,
        'code' => 'COT-B',
        'quotation_date' => now()->toDateString(),
        'currency' => 'PEN',
        'subtotal' => 950,
        'tax' => 171,
        'total' => 1121,
        'delivery_time' => 7,
    ]);

    Livewire::test(SelectWinningQuotation::class, ['purchaseRequest' => $purchaseRequest])
        ->set('selected_supplier_quotation_id', $quotationB->id)
        ->set('selection_reason', 'Mejor precio global para la obra.')
        ->call('saveSelection')
        ->call('generateOrder')
        ->assertHasNoErrors();

    $purchaseRequest->refresh();

    $this->assertDatabaseHas('quotation_comparisons', [
        'purchase_request_id' => $purchaseRequest->id,
        'selected_supplier_quotation_id' => $quotationB->id,
    ]);

    expect($purchaseRequest->status)->toBe('orden_generada');
    expect($purchaseRequest->comparison?->purchase_order_code)->toBe('OC-SC-003');
});

test('purchase pages and comparison pdf routes render for authorized users', function () {
    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'requested_by' => $this->user->id,
        'code' => 'SC-004',
        'priority' => 'media',
        'request_date' => now()->toDateString(),
        'description' => 'Compra de accesorios',
        'status' => 'cotizada',
    ]);

    $quotation = SupplierQuotation::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'purchase_request_id' => $purchaseRequest->id,
        'supplier_id' => $this->supplierA->id,
        'code' => 'COT-004',
        'quotation_date' => now()->toDateString(),
        'currency' => 'PEN',
        'subtotal' => 1000,
        'tax' => 180,
        'total' => 1180,
        'delivery_time' => 4,
    ]);

    $purchaseRequest->comparison()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'selected_supplier_quotation_id' => $quotation->id,
        'selected_by' => $this->user->id,
        'compared_at' => now(),
        'selection_reason' => 'Oferta mas conveniente.',
        'purchase_order_code' => 'OC-SC-004',
        'purchase_order_generated_at' => now(),
    ]);

    $this->get(route('modules.purchases'))->assertOk()->assertSee('Solicitudes de compra');
    $this->get(route('purchases.quotations', $purchaseRequest))->assertOk()->assertSee('Cotizaciones por solicitud');
    $this->get(route('purchases.comparison', $purchaseRequest))->assertOk()->assertSee('Comparativa visual');
    $this->get(route('purchases.winner', $purchaseRequest))->assertOk()->assertSee('Seleccion de ganador');
    $this->get(route('purchases.comparison.pdf', $purchaseRequest))->assertOk()->assertHeader('content-type', 'application/pdf');
    $this->get(route('purchases.order.pdf', $purchaseRequest))->assertOk()->assertHeader('content-type', 'application/pdf');
});
