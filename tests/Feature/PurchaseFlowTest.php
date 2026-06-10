<?php

use App\Enums\QuotationCaptureMode;
use App\Livewire\Purchases\ManagePurchaseRequests;
use App\Livewire\Purchases\ManageSupplierQuotations;
use App\Livewire\Purchases\ShowQuotationComparison;
use App\Models\Company;
use App\Models\Order;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\UploadedFile;
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
        'code' => 'OBR001',
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

    $purchaseRequest = PurchaseRequest::query()->where('description', 'Compra de materiales electricos')->firstOrFail();

    $this->assertDatabaseHas('requirements', [
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'description' => 'Compra de materiales electricos',
    ]);

    expect($purchaseRequest->code)->toContain('REQ');

    expect($purchaseRequest->items()->count())->toBe(1);
});

test('supplier quotations can be created and totals are calculated', function () {
    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'responsible_user_id' => $this->user->id,
        'requested_by' => $this->user->id,
        'code' => 'SC-002',
        'priority' => 'media',
        'request_date' => now()->toDateString(),
        'description' => 'Compra de tuberias',
        'status' => 'creado',
    ]);

    $purchaseRequest->items()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'item_type' => 'material',
        'description' => 'Tubo PVC',
        'unit' => 'und',
        'quantity' => 10,
        'technical_specification' => '4 pulgadas',
    ]);

    Livewire::test(ManageSupplierQuotations::class, ['purchaseRequest' => $purchaseRequest])
        ->call('openCreateModal')
        ->set('supplier_id', $this->supplierA->id)
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

    $quotation = SupplierQuotation::query()->where('requirement_id', $purchaseRequest->id)->firstOrFail();

    expect($quotation->code)->toContain('COT');

    expect((float) $quotation->subtotal)->toBe(500.0);
    expect((float) $quotation->total)->toBe(680.0);
    expect($purchaseRequest->fresh()->status)->toBe('en_proceso');
});

test('supplier quotations modal filters suppliers by search term', function () {
    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'responsible_user_id' => $this->user->id,
        'requested_by' => $this->user->id,
        'code' => 'SC-SRCH',
        'priority' => 'media',
        'request_date' => now()->toDateString(),
        'description' => 'Busqueda de proveedor',
        'status' => 'creado',
    ]);

    Livewire::test(ManageSupplierQuotations::class, ['purchaseRequest' => $purchaseRequest])
        ->call('openCreateModal')
        ->set('supplier_search', 'Proveedor B')
        ->assertSee('Proveedor B')
        ->assertDontSee('Proveedor A')
        ->call('selectSupplier', $this->supplierB->id)
        ->assertSet('supplier_id', (string) $this->supplierB->id)
        ->assertSet('supplier_search', 'Proveedor B');
});

test('supplier quotations validate required fields in form mode', function () {
    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'responsible_user_id' => $this->user->id,
        'requested_by' => $this->user->id,
        'code' => 'SC-VAL',
        'priority' => 'media',
        'request_date' => now()->toDateString(),
        'description' => 'Validación cotización',
        'status' => 'creado',
    ]);

    $component = Livewire::test(ManageSupplierQuotations::class, ['purchaseRequest' => $purchaseRequest])
        ->call('openCreateModal')
        ->set('items', [])
        ->call('saveQuotation')
        ->assertHasErrors(['supplier_id', 'items']);

    expect($component->errors()->first('supplier_id'))->toBe('El campo proveedor es obligatorio.');
    expect($component->errors()->first('items'))->toBe('El campo ítems cotizados es obligatorio.');
});

test('supplier quotations can be registered from uploaded pdf', function () {
    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'responsible_user_id' => $this->user->id,
        'requested_by' => $this->user->id,
        'code' => 'SC-PDF',
        'priority' => 'media',
        'request_date' => now()->toDateString(),
        'description' => 'Compra con cotización PDF',
        'status' => 'creado',
    ]);

    Livewire::test(ManageSupplierQuotations::class, ['purchaseRequest' => $purchaseRequest])
        ->call('openCreateModal')
        ->set('capture_mode', QuotationCaptureMode::Pdf->value())
        ->set('supplier_id', $this->supplierA->id)
        ->set('currency', 'PEN')
        ->set('subtotal', '500')
        ->set('tax', '90')
        ->set('delivery_time', '4')
        ->set('quotation_pdf', UploadedFile::fake()->create('cotizacion-proveedor.pdf', 200, 'application/pdf'))
        ->call('saveQuotation')
        ->assertHasNoErrors();

    $quotation = SupplierQuotation::query()->where('requirement_id', $purchaseRequest->id)->firstOrFail();

    expect($quotation->capture_mode)->toBe(QuotationCaptureMode::Pdf->value());
    expect((float) $quotation->subtotal)->toBe(500.0);
    expect((float) $quotation->total)->toBe(590.0);
    expect($quotation->items)->toHaveCount(0);
    expect($quotation->getFirstMedia('cotizacion_pdf'))->not->toBeNull();

    $previewResponse = $this->get(route('purchases.quotations.pdf', $quotation));

    $previewResponse->assertOk();
    $previewResponse->assertHeader('content-type', 'application/pdf');
    expect(str_contains(strtolower((string) $previewResponse->headers->get('content-disposition')), 'inline'))->toBeTrue();

    Livewire::test(ManageSupplierQuotations::class, ['purchaseRequest' => $purchaseRequest])
        ->call('openPdfModal', $quotation->id)
        ->assertSet('showPdfModal', true)
        ->assertSet('pdfViewerUrl', route('purchases.quotations.pdf', $quotation, absolute: false));
});

test('form quotation preview generates inline pdf for comparison', function () {
    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'responsible_user_id' => $this->user->id,
        'requested_by' => $this->user->id,
        'code' => 'SC-FRM',
        'priority' => 'media',
        'request_date' => now()->toDateString(),
        'description' => 'Cotización formulario',
        'status' => 'creado',
    ]);

    Livewire::test(ManageSupplierQuotations::class, ['purchaseRequest' => $purchaseRequest])
        ->call('openCreateModal')
        ->set('supplier_id', $this->supplierA->id)
        ->set('currency', 'PEN')
        ->set('tax', '90')
        ->set('delivery_time', '4')
        ->set('items', [
            [
                'product_or_service' => 'Cable THW',
                'unit' => 'rollo',
                'quantity' => '5',
                'unit_price' => '100',
            ],
        ])
        ->call('saveQuotation')
        ->assertHasNoErrors();

    $quotation = SupplierQuotation::query()->where('requirement_id', $purchaseRequest->id)->firstOrFail();

    expect($quotation->capture_mode)->toBe(QuotationCaptureMode::Form->value());
    expect($quotation->getFirstMedia('cotizacion_pdf'))->toBeNull();

    $previewResponse = $this->get(route('purchases.quotations.pdf', $quotation));

    $previewResponse->assertOk();
    $previewResponse->assertHeader('content-type', 'application/pdf');
    expect(str_starts_with($previewResponse->getContent(), '%PDF'))->toBeTrue();

    Livewire::test(ManageSupplierQuotations::class, ['purchaseRequest' => $purchaseRequest->fresh()])
        ->call('openComparisonModal')
        ->assertSet('showComparisonModal', false);

    Livewire::test(ManageSupplierQuotations::class, ['purchaseRequest' => $purchaseRequest->fresh()])
        ->call('openCreateModal')
        ->set('supplier_id', $this->supplierB->id)
        ->set('currency', 'PEN')
        ->set('tax', '90')
        ->set('delivery_time', '4')
        ->set('items', [
            [
                'product_or_service' => 'Cable THW B',
                'unit' => 'rollo',
                'quantity' => '5',
                'unit_price' => '90',
            ],
        ])
        ->call('saveQuotation')
        ->assertHasNoErrors();

    $quotationIds = SupplierQuotation::query()
        ->where('requirement_id', $purchaseRequest->id)
        ->pluck('id')
        ->all();

    Livewire::test(ManageSupplierQuotations::class, ['purchaseRequest' => $purchaseRequest->fresh()])
        ->set('comparison_quotation_ids', $quotationIds)
        ->call('openComparisonModal')
        ->assertSet('showComparisonModal', true)
        ->assertSeeHtml('iframe');
});

test('comparison modal requires at least two selected quotations', function () {
    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'responsible_user_id' => $this->user->id,
        'requested_by' => $this->user->id,
        'code' => 'SC-MIN',
        'priority' => 'media',
        'request_date' => now()->toDateString(),
        'description' => 'Mínimo comparativa',
        'status' => 'en_proceso',
    ]);

    Livewire::test(ManageSupplierQuotations::class, ['purchaseRequest' => $purchaseRequest])
        ->call('openCreateModal')
        ->set('supplier_id', $this->supplierA->id)
        ->set('currency', 'PEN')
        ->set('tax', '90')
        ->set('delivery_time', '4')
        ->set('items', [[
            'product_or_service' => 'Cable THW',
            'unit' => 'rollo',
            'quantity' => '5',
            'unit_price' => '100',
        ]])
        ->call('saveQuotation')
        ->assertHasNoErrors();

    $quotationId = SupplierQuotation::query()->where('requirement_id', $purchaseRequest->id)->value('id');

    Livewire::test(ManageSupplierQuotations::class, ['purchaseRequest' => $purchaseRequest->fresh()])
        ->set('comparison_quotation_ids', [$quotationId])
        ->call('openComparisonModal')
        ->assertSet('showComparisonModal', false);
});

test('comparison modal opens fullscreen with quotation pdf previews', function () {
    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'responsible_user_id' => $this->user->id,
        'requested_by' => $this->user->id,
        'code' => 'SC-CMP',
        'priority' => 'media',
        'request_date' => now()->toDateString(),
        'description' => 'Comparativa PDF',
        'status' => 'en_proceso',
    ]);

    foreach ([$this->supplierA, $this->supplierB] as $index => $supplier) {
        Livewire::test(ManageSupplierQuotations::class, ['purchaseRequest' => $purchaseRequest])
            ->call('openCreateModal')
            ->set('capture_mode', QuotationCaptureMode::Pdf->value())
            ->set('supplier_id', $supplier->id)
            ->set('currency', 'PEN')
            ->set('subtotal', $index === 0 ? '500' : '450')
            ->set('tax', $index === 0 ? '90' : '81')
            ->set('delivery_time', $index === 0 ? '5' : '3')
            ->set('quotation_pdf', UploadedFile::fake()->create("cotizacion-{$index}.pdf", 200, 'application/pdf'))
            ->call('saveQuotation')
            ->assertHasNoErrors();
    }

    $quotationIds = SupplierQuotation::query()
        ->where('requirement_id', $purchaseRequest->id)
        ->pluck('id')
        ->all();

    Livewire::test(ManageSupplierQuotations::class, ['purchaseRequest' => $purchaseRequest->fresh()])
        ->set('comparison_quotation_ids', $quotationIds)
        ->call('openComparisonModal')
        ->assertSet('showComparisonModal', true)
        ->assertSee('Comparativa de cotizaciones')
        ->assertSee($this->supplierA->business_name)
        ->assertSee($this->supplierB->business_name)
        ->assertSeeHtml('iframe');
});

test('winner selection and purchase order generation are persisted', function () {
    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'responsible_user_id' => $this->user->id,
        'requested_by' => $this->user->id,
        'code' => 'SC-003',
        'priority' => 'alta',
        'request_date' => now()->toDateString(),
        'description' => 'Compra de equipos',
        'status' => 'en_proceso',
    ]);

    $quotationA = SupplierQuotation::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'requirement_id' => $purchaseRequest->id,
        'supplier_id' => $this->supplierA->id,
        'code' => 'COT-A',
        'quotation_date' => now()->toDateString(),
        'currency' => 'PEN',
        'subtotal' => 1000,
        'tax' => 180,
        'total' => 1180,
        'delivery_time_days' => 5,
        'status' => 'registrada',
    ]);

    $quotationB = SupplierQuotation::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'requirement_id' => $purchaseRequest->id,
        'supplier_id' => $this->supplierB->id,
        'code' => 'COT-B',
        'quotation_date' => now()->toDateString(),
        'currency' => 'PEN',
        'subtotal' => 950,
        'tax' => 171,
        'total' => 1121,
        'delivery_time_days' => 7,
        'status' => 'registrada',
    ]);

    Livewire::test(ShowQuotationComparison::class, ['purchaseRequest' => $purchaseRequest])
        ->call('openWinnerModal')
        ->set('selected_supplier_quotation_id', $quotationB->id)
        ->set('selection_reason', 'Mejor precio global para la obra.')
        ->call('saveSelection')
        ->call('openWinnerModal')
        ->call('generateOrder')
        ->assertHasNoErrors();

    $purchaseRequest->refresh();

    $this->assertDatabaseHas('quotation_comparisons', [
        'requirement_id' => $purchaseRequest->id,
        'selected_supplier_quotation_id' => $quotationB->id,
    ]);

    expect($purchaseRequest->status)->toBe('atendido');
    expect(Order::query()->where('requirement_id', $purchaseRequest->id)->exists())->toBeTrue();

    $order = Order::query()->where('requirement_id', $purchaseRequest->id)->firstOrFail();

    expect($order->code)->not->toMatch('/-OC-OC-/');
    expect($order->code)->toMatch('/-OBR001-OC-\d{4}-\d{6}$/');
    expect($purchaseRequest->comparison?->order_code)->toBe($order->code);
});

test('changing the winning quotation after order generation assigns a new order code', function () {
    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'responsible_user_id' => $this->user->id,
        'requested_by' => $this->user->id,
        'code' => 'SC-005',
        'priority' => 'alta',
        'request_date' => now()->toDateString(),
        'description' => 'Compra con cambio de ganador',
        'status' => 'en_proceso',
    ]);

    $quotationA = SupplierQuotation::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'requirement_id' => $purchaseRequest->id,
        'supplier_id' => $this->supplierA->id,
        'code' => 'COT-CHANGE-A',
        'quotation_date' => now()->toDateString(),
        'currency' => 'PEN',
        'subtotal' => 1000,
        'tax' => 180,
        'total' => 1180,
        'delivery_time_days' => 5,
        'status' => 'registrada',
    ]);

    $quotationB = SupplierQuotation::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'requirement_id' => $purchaseRequest->id,
        'supplier_id' => $this->supplierB->id,
        'code' => 'COT-CHANGE-B',
        'quotation_date' => now()->toDateString(),
        'currency' => 'PEN',
        'subtotal' => 950,
        'tax' => 171,
        'total' => 1121,
        'delivery_time_days' => 7,
        'status' => 'registrada',
    ]);

    $component = Livewire::test(ShowQuotationComparison::class, ['purchaseRequest' => $purchaseRequest]);

    $component
        ->call('openWinnerModal')
        ->set('selected_supplier_quotation_id', $quotationA->id)
        ->set('selection_reason', 'Primera selección.')
        ->call('saveSelection')
        ->call('openWinnerModal')
        ->call('generateOrder')
        ->assertHasNoErrors();

    $firstOrder = Order::query()->where('supplier_quotation_id', $quotationA->id)->firstOrFail();

    $component
        ->call('openWinnerModal')
        ->set('selected_supplier_quotation_id', $quotationB->id)
        ->set('selection_reason', 'Cambio por mejor precio.')
        ->call('saveSelection')
        ->call('openWinnerModal')
        ->call('generateOrder')
        ->assertHasNoErrors();

    $secondOrder = Order::query()->where('supplier_quotation_id', $quotationB->id)->firstOrFail();

    expect($secondOrder->id)->not->toBe($firstOrder->id);
    expect($secondOrder->code)->not->toBe($firstOrder->code);
    expect($secondOrder->code)->not->toMatch('/-OC-OC-/');
});

test('purchase pages and comparison pdf routes render for authorized users', function () {
    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'responsible_user_id' => $this->user->id,
        'requested_by' => $this->user->id,
        'code' => 'SC-004',
        'priority' => 'media',
        'request_date' => now()->toDateString(),
        'description' => 'Compra de accesorios',
        'status' => 'en_proceso',
    ]);

    $quotation = SupplierQuotation::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'requirement_id' => $purchaseRequest->id,
        'supplier_id' => $this->supplierA->id,
        'code' => 'COT-004',
        'quotation_date' => now()->toDateString(),
        'currency' => 'PEN',
        'subtotal' => 1000,
        'tax' => 180,
        'total' => 1180,
        'delivery_time_days' => 4,
        'status' => 'registrada',
    ]);

    $purchaseRequest->comparison()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'selected_supplier_quotation_id' => $quotation->id,
        'selected_by' => $this->user->id,
        'compared_at' => now(),
        'selection_reason' => 'Oferta mas conveniente.',
        'order_code' => 'OC-SC-004',
        'order_generated_at' => now(),
    ]);

    $this->get(route('modules.purchases'))->assertOk()->assertSee('Requerimientos');
    $this->get(route('purchases.quotations', $purchaseRequest))->assertOk();
    $this->get(route('purchases.comparison', $purchaseRequest))->assertOk();
    $this->get(route('purchases.winner', $purchaseRequest))
        ->assertRedirect(route('purchases.comparison', $purchaseRequest).'?selectWinner=1');
    $this->get(route('purchases.comparison.pdf', $purchaseRequest))->assertOk()->assertHeader('content-type', 'application/pdf');
    $this->get(route('purchases.order.pdf', $purchaseRequest))->assertOk()->assertHeader('content-type', 'application/pdf');
});
