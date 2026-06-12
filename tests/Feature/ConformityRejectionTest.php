<?php

use App\Actions\Purchases\GeneratePurchaseOrder;
use App\Enums\ConformityResult;
use App\Livewire\Purchases\ManagePurchaseOrders;
use App\Models\AccountsPayable;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use Livewire\Livewire;

test('rejected conformity requires observation and does not create accounts payable', function () {
    ['company' => $company, 'user' => $user] = authenticateWithCompany();

    $project = Project::factory()->create([
        'company_id' => $company->id,
        'responsible_user_id' => $user->id,
    ]);

    $supplier = Supplier::factory()->create(['company_id' => $company->id]);

    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $company->id,
        'work_project_id' => $project->id,
        'requested_by' => $user->id,
        'code' => 'SC-REJ-01',
        'priority' => 'alta',
        'request_date' => now()->toDateString(),
        'description' => 'Compra para rechazo',
        'status' => 'en_proceso',
    ]);

    $quotation = SupplierQuotation::query()->create([
        'company_id' => $company->id,
        'work_project_id' => $project->id,
        'requirement_id' => $purchaseRequest->id,
        'supplier_id' => $supplier->id,
        'code' => 'COT-REJ',
        'quotation_date' => now()->toDateString(),
        'currency' => 'PEN',
        'subtotal' => 100,
        'tax' => 18,
        'total' => 118,
        'delivery_time_days' => 3,
        'status' => 'registrada',
    ]);

    $purchaseRequest->comparison()->create([
        'company_id' => $company->id,
        'work_project_id' => $project->id,
        'selected_supplier_quotation_id' => $quotation->id,
        'selected_by' => $user->id,
        'compared_at' => now(),
        'selection_reason' => 'Única oferta',
        'order_code' => 'OC-REJ-01',
        'order_generated_at' => now(),
    ]);

    $purchaseOrder = app(GeneratePurchaseOrder::class)->handle($purchaseRequest);

    Livewire::test(ManagePurchaseOrders::class)
        ->call('openConformityModal', $purchaseOrder->id)
        ->set('conformity_result', ConformityResult::Rejected->value())
        ->set('conformity_date', now()->toDateString())
        ->call('saveConformity')
        ->assertHasErrors(['conformity_observation'])
        ->set('conformity_observation', 'Material no cumple especificación.')
        ->call('saveConformity')
        ->assertHasNoErrors();

    expect($purchaseOrder->fresh()->status)->toBe('rechazada');
    expect(AccountsPayable::query()->where('order_id', $purchaseOrder->id)->exists())->toBeFalse();

    $this->assertDatabaseHas('audits', [
        'company_id' => $company->id,
        'user_id' => $user->id,
        'action' => 'conformidad_registrada',
        'auditable_id' => $purchaseOrder->id,
    ]);
});
