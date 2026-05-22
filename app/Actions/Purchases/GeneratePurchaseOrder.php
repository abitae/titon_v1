<?php

namespace App\Actions\Purchases;

use App\Enums\CorrelativeSubject;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\RequirementStatus;
use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\Requirement;
use App\Services\Codes\CodeGeneratorService;

class GeneratePurchaseOrder
{
    public function handle(Requirement $requirement): PurchaseOrder
    {
        $comparison = $requirement->comparison()->with('selectedQuotation.items')->firstOrFail();
        $quotation = $comparison->selectedQuotation;
        $project = $requirement->project()->firstOrFail();
        $company = Company::query()->findOrFail($requirement->company_id);

        $orderType = $requirement->requirement_type === 'servicio'
            ? OrderType::Service
            : OrderType::Purchase;

        $existingOrder = PurchaseOrder::query()
            ->where('supplier_quotation_id', $quotation->id)
            ->first();

        $code = $existingOrder?->code ?? ($comparison->order_code ?: null);

        if ($code === null || $code === '') {
            $code = app(CodeGeneratorService::class)->generate(
                $company,
                $project,
                CorrelativeSubject::Order,
                $orderType,
            );
        }

        $order = PurchaseOrder::query()->updateOrCreate(
            ['supplier_quotation_id' => $quotation->id],
            [
                'company_id' => $requirement->company_id,
                'work_project_id' => $requirement->work_project_id,
                'requirement_id' => $requirement->id,
                'supplier_id' => $quotation->supplier_id,
                'code' => $code,
                'order_type' => $orderType->value(),
                'issue_date' => now()->toDateString(),
                'currency' => $quotation->currency,
                'subtotal' => $quotation->subtotal,
                'tax' => $quotation->tax,
                'total' => $quotation->total,
                'status' => OrderStatus::Issued->value(),
                'conditions' => $quotation->payment_conditions,
                'observation' => $quotation->observation,
            ],
        );

        $order->items()->delete();

        foreach ($quotation->items as $item) {
            $order->items()->create([
                'company_id' => $order->company_id,
                'work_project_id' => $order->work_project_id,
                'description' => $item->product_or_service ?? $item->description ?? '',
                'unit' => $item->unit,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
            ]);
        }

        $comparison->update([
            'order_code' => $order->code,
            'order_generated_at' => now(),
        ]);

        $requirement->update([
            'status' => RequirementStatus::Attended->value(),
        ]);

        return $order->refresh();
    }
}
