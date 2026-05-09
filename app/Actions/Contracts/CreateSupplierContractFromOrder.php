<?php

namespace App\Actions\Contracts;

use App\Enums\CorrelativeSubject;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierContractStatus;
use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\SupplierContract;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;

class CreateSupplierContractFromOrder
{
    public function handle(PurchaseOrder $purchaseOrder): SupplierContract
    {
        $company = Company::query()->findOrFail($purchaseOrder->company_id);
        $existing = SupplierContract::query()
            ->where('purchase_order_id', $purchaseOrder->id)
            ->first();

        $contractNumber = $existing?->contract_number;

        if ($contractNumber === null || $contractNumber === '') {
            $contractNumber = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::SupplierContract);
        }

        $contract = SupplierContract::query()->updateOrCreate(
            ['purchase_order_id' => $purchaseOrder->id],
            [
                'company_id' => $purchaseOrder->company_id,
                'work_project_id' => $purchaseOrder->work_project_id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'contract_number' => $contractNumber,
                'contract_type' => 'Suministro',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'total_amount' => $purchaseOrder->total,
                'currency' => $purchaseOrder->currency,
                'payment_conditions' => $purchaseOrder->conditions,
                'penalties' => null,
                'guarantees' => null,
                'status' => SupplierContractStatus::Draft->value(),
                'observation' => $purchaseOrder->observation,
            ],
        );

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::ConvertedToContract->value(),
        ]);

        return $contract->refresh();
    }
}
