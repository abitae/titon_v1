<?php

namespace App\Actions\Purchases;

use App\Enums\CorrelativeSubject;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\RequirementStatus;
use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\Requirement;
use App\Models\RequirementItem;
use App\Models\SupplierQuotation;
use App\Models\SupplierQuotationItem;
use App\Services\Codes\CodeGeneratorService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeneratePurchaseOrder
{
    public function __construct(
        protected AttachQuotationPdfToOrder $attachQuotationPdfToOrder,
    ) {}

    public function handle(Requirement $requirement): PurchaseOrder
    {
        return DB::transaction(function () use ($requirement): PurchaseOrder {
            return $this->persistPurchaseOrder($requirement);
        });
    }

    protected function persistPurchaseOrder(Requirement $requirement): PurchaseOrder
    {
        $requirement->load(['items', 'comparison.selectedQuotation.items']);

        $comparison = $requirement->comparison;

        if ($comparison === null) {
            abort(404, 'No existe una comparativa para generar la orden.');
        }

        $quotation = $comparison->selectedQuotation;

        if ($quotation === null) {
            abort(404, 'No hay una cotización ganadora seleccionada.');
        }

        $quotation->loadMissing('media');
        $project = $requirement->project()->firstOrFail();
        $company = Company::query()->findOrFail($requirement->company_id);

        $orderType = $requirement->requirement_type === 'servicio'
            ? OrderType::Service
            : OrderType::Purchase;

        $existingOrder = PurchaseOrder::query()
            ->where('supplier_quotation_id', $quotation->id)
            ->first();

        $code = $existingOrder?->code;

        if ($code === null || $code === '') {
            $storedCode = trim((string) ($comparison->order_code ?? ''));

            if ($storedCode !== '') {
                $codeOwner = PurchaseOrder::query()
                    ->where('company_id', $requirement->company_id)
                    ->where('code', $storedCode)
                    ->first();

                if ($codeOwner === null || $codeOwner->supplier_quotation_id === $quotation->id) {
                    $code = $storedCode;
                }
            }
        }

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

        $this->syncOrderItems($order, $requirement, $quotation);
        $this->attachQuotationPdfToOrder->handle($order, $quotation);

        $comparison->update([
            'order_code' => $order->code,
            'order_generated_at' => now(),
        ]);

        $requirement->update([
            'status' => RequirementStatus::Attended->value(),
        ]);

        return $order->load('items')->refresh();
    }

    protected function syncOrderItems(PurchaseOrder $order, Requirement $requirement, SupplierQuotation $quotation): void
    {
        $order->items()->delete();

        if ($quotation->items->isNotEmpty()) {
            foreach ($quotation->items as $item) {
                $this->createOrderItemFromQuotationItem($order, $item);
            }

            return;
        }

        /** @var Collection<int, RequirementItem> $requirementItems */
        $requirementItems = $requirement->items;

        foreach ($requirementItems as $item) {
            $quantity = (float) $item->quantity;
            $unitPrice = (float) ($item->estimated_unit_price ?? 0);
            $total = (float) ($item->estimated_total ?? 0);

            if ($total <= 0 && $unitPrice > 0) {
                $total = $unitPrice * $quantity;
            }

            if ($total <= 0 && $unitPrice <= 0 && $requirementItems->count() === 1) {
                $total = (float) $quotation->subtotal;
                $unitPrice = $quantity > 0 ? $total / $quantity : $total;
            }

            $order->items()->create([
                'company_id' => $order->company_id,
                'work_project_id' => $order->work_project_id,
                'description' => trim((string) $item->description),
                'unit' => $item->unit,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $total > 0 ? $total : $unitPrice * $quantity,
            ]);
        }
    }

    protected function createOrderItemFromQuotationItem(PurchaseOrder $order, SupplierQuotationItem $item): void
    {
        $order->items()->create([
            'company_id' => $order->company_id,
            'work_project_id' => $order->work_project_id,
            'description' => trim((string) ($item->product_or_service ?? '')),
            'unit' => $item->unit,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'total' => $item->total,
        ]);
    }
}
