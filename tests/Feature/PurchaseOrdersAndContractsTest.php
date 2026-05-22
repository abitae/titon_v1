<?php

use App\Actions\Contracts\CreateSupplierContractFromOrder;
use App\Actions\Purchases\GeneratePurchaseOrder;
use App\Livewire\Contracts\ManageSupplierContracts;
use App\Livewire\Purchases\ManagePurchaseOrders;
use App\Models\Company;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->company = Company::factory()->create([
        'correlative_prefix' => 'TITON',
    ]);
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

    $this->supplier = Supplier::factory()->create([
        'company_id' => $this->company->id,
    ]);

    $this->purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'requested_by' => $this->user->id,
        'code' => 'SC-100',
        'priority' => 'alta',
        'request_date' => now()->toDateString(),
        'description' => 'Compra base para pruebas',
        'status' => 'en_proceso',
    ]);

    $this->quotation = SupplierQuotation::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'requirement_id' => $this->purchaseRequest->id,
        'supplier_id' => $this->supplier->id,
        'code' => 'COT-100',
        'quotation_date' => now()->toDateString(),
        'currency' => 'PEN',
        'subtotal' => 1000,
        'tax' => 180,
        'total' => 1180,
        'delivery_time_days' => 4,
        'status' => 'registrada',
        'payment_conditions' => 'Contado',
        'warranty' => '12 meses',
    ]);

    $this->quotation->items()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'product_or_service' => 'Material A',
        'unit' => 'und',
        'quantity' => 10,
        'unit_price' => 100,
        'total' => 1000,
    ]);

    $this->purchaseRequest->comparison()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'selected_supplier_quotation_id' => $this->quotation->id,
        'selected_by' => $this->user->id,
        'compared_at' => now(),
        'selection_reason' => 'Mejor oferta',
        'order_code' => 'OC-SC-100',
        'order_generated_at' => now(),
    ]);
});

test('purchase order generation creates real order records and items', function () {
    $purchaseOrder = app(GeneratePurchaseOrder::class)->handle($this->purchaseRequest);

    expect($purchaseOrder)->toBeInstanceOf(PurchaseOrder::class);
    expect($purchaseOrder->code)->toBe('OC-SC-100');
    expect($purchaseOrder->items()->count())->toBe(1);
    expect($this->purchaseRequest->fresh()->status)->toBe('atendido');
});

test('purchase orders can be approved observed cancelled and converted to contracts', function () {
    $purchaseOrder = app(GeneratePurchaseOrder::class)->handle($this->purchaseRequest);

    Livewire::test(ManagePurchaseOrders::class)
        ->call('openDetailModal', $purchaseOrder->id)
        ->set('approval_notes', 'Aprobacion de gerencia')
        ->call('approveOrder')
        ->set('observation', 'Revisar plazo de entrega')
        ->call('observeOrder')
        ->set('cancellation_reason', 'Orden reemplazada')
        ->call('cancelOrder')
        ->call('createContract')
        ->assertHasNoErrors();

    $purchaseOrder->refresh();

    expect($purchaseOrder->contract)->not->toBeNull();
    expect($purchaseOrder->contract?->contract_number)->toMatch('/^TITON-CON-\d{4}-\d{6}$/');
});

test('contracts support signed file upload and approval lifecycle', function () {
    Storage::fake('public');

    $purchaseOrder = app(GeneratePurchaseOrder::class)->handle($this->purchaseRequest);
    $contract = app(CreateSupplierContractFromOrder::class)->handle($purchaseOrder);

    Livewire::test(ManageSupplierContracts::class)
        ->call('openDetailModal', $contract->id)
        ->set('approval_notes', 'Contrato validado')
        ->call('approveContract')
        ->set('cancellation_reason', 'Se deja sin efecto')
        ->call('cancelContract')
        ->set('signed_contract_files', [UploadedFile::fake()->create('contrato-firmado.pdf', 200, 'application/pdf')])
        ->call('uploadSignedContract')
        ->assertHasNoErrors();

    $contract->refresh();

    expect($contract->getMedia('signed_contract'))->toHaveCount(1);
    expect($contract->status)->toBe('anulado');
});

test('orders and contracts pages plus pdf routes render for authorized users', function () {
    $purchaseOrder = app(GeneratePurchaseOrder::class)->handle($this->purchaseRequest);
    $contract = app(CreateSupplierContractFromOrder::class)->handle($purchaseOrder);

    $this->get(route('purchases.orders'))->assertOk()->assertSee('Ordenes de compra');
    $this->get(route('modules.contracts'))->assertOk()->assertSee('Contratos con proveedores');
    $this->get(route('purchases.orders.pdf', $purchaseOrder))->assertOk()->assertHeader('content-type', 'application/pdf');
    $this->get(route('contracts.pdf', $contract))->assertOk()->assertHeader('content-type', 'application/pdf');
});
