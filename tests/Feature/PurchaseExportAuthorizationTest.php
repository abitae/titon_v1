<?php

use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\SupplierQuotation;

test('users without purchases export permission cannot download comparison pdf', function () {
    ['company' => $company, 'user' => $user] = authenticateWithCompany('Responsable de Obra');

    $project = Project::factory()->create([
        'company_id' => $company->id,
        'responsible_user_id' => $user->id,
    ]);

    $supplier = Supplier::factory()->create(['company_id' => $company->id]);

    $purchaseRequest = PurchaseRequest::query()->create([
        'company_id' => $company->id,
        'work_project_id' => $project->id,
        'requested_by' => $user->id,
        'code' => 'SC-AUTH',
        'priority' => 'media',
        'request_date' => now()->toDateString(),
        'description' => 'Prueba permisos exportación',
        'status' => 'en_proceso',
    ]);

    $quotation = SupplierQuotation::query()->create([
        'company_id' => $company->id,
        'work_project_id' => $project->id,
        'requirement_id' => $purchaseRequest->id,
        'supplier_id' => $supplier->id,
        'code' => 'COT-AUTH',
        'quotation_date' => now()->toDateString(),
        'currency' => 'PEN',
        'subtotal' => 100,
        'tax' => 18,
        'total' => 118,
        'delivery_time_days' => 2,
        'status' => 'registrada',
    ]);

    $purchaseRequest->comparison()->create([
        'company_id' => $company->id,
        'work_project_id' => $project->id,
        'selected_supplier_quotation_id' => $quotation->id,
        'selected_by' => $user->id,
        'compared_at' => now(),
        'selection_reason' => 'Única cotización',
    ]);

    $this->get(route('purchases.comparison.pdf', $purchaseRequest))->assertForbidden();

    $purchaseOrder = PurchaseOrder::query()->create([
        'company_id' => $company->id,
        'work_project_id' => $project->id,
        'supplier_id' => $supplier->id,
        'supplier_quotation_id' => $quotation->id,
        'requirement_id' => $purchaseRequest->id,
        'code' => 'OC-AUTH-01',
        'issue_date' => now()->toDateString(),
        'currency' => 'PEN',
        'subtotal' => 100,
        'tax' => 18,
        'total' => 118,
        'status' => 'emitida',
    ]);

    $this->get(route('purchases.orders.pdf', $purchaseOrder))->assertForbidden();
    $this->get(route('purchases.orders.pdf.preview', $purchaseOrder))->assertOk();
});
