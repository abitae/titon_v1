<?php

namespace App\Actions\Purchases;

use App\Enums\CorrelativeSubject;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;

class GeneratePurchaseOrder
{
    public function handle(PurchaseRequest $purchaseRequest): PurchaseOrder
    {
        $comparison = $purchaseRequest->comparison()->with('selectedQuotation.items')->firstOrFail();
        $quotation = $comparison->selectedQuotation;

        $company = Company::query()->findOrFail($purchaseRequest->company_id);
        $issuer = app(IssueCompanyCorrelativeCode::class);

        $existingOrder = PurchaseOrder::query()
            ->where('supplier_quotation_id', $quotation->id)
            ->first();

        $code = $existingOrder?->code
            ?? ($comparison->purchase_order_code ?: null);

        if ($code === null || $code === '') {
            $code = $issuer->issue($company, CorrelativeSubject::PurchaseOrder);
        }

        $purchaseOrder = PurchaseOrder::query()->updateOrCreate(
            ['supplier_quotation_id' => $quotation->id],
            [
                'company_id' => $purchaseRequest->company_id,
                'work_project_id' => $purchaseRequest->work_project_id,
                'supplier_id' => $quotation->supplier_id,
                'code' => $code,
                'issue_date' => now()->toDateString(),
                'currency' => $quotation->currency,
                'subtotal' => $quotation->subtotal,
                'tax' => $quotation->tax,
                'total' => $quotation->total,
                'status' => PurchaseOrderStatus::Generated->value(),
                'conditions' => $quotation->payment_conditions,
                'observation' => $quotation->observation,
            ],
        );

        $purchaseOrder->items()->delete();

        foreach ($quotation->items as $item) {
            $purchaseOrder->items()->create([
                'company_id' => $purchaseOrder->company_id,
                'work_project_id' => $purchaseOrder->work_project_id,
                'product_or_service' => $item->product_or_service,
                'unit' => $item->unit,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
            ]);
        }

        $comparison->update([
            'purchase_order_code' => $purchaseOrder->code,
            'purchase_order_generated_at' => now(),
        ]);

        $purchaseRequest->update([
            'status' => PurchaseRequestStatus::Ordered->value(),
        ]);

        return $purchaseOrder->refresh();
    }
}
