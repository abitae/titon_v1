<?php

namespace App\Concerns;

use App\Actions\Purchases\GeneratePurchaseOrder;
use App\Actions\Purchases\UpsertQuotationComparison;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\SupplierQuotation;

trait SelectsWinningQuotation
{
    public bool $showWinnerModal = false;

    public ?int $selected_supplier_quotation_id = null;

    public string $selection_reason = '';

    public ?int $generated_purchase_order_id = null;

    protected function bootWinningQuotationSelection(PurchaseRequest $purchaseRequest): void
    {
        $this->selected_supplier_quotation_id = $purchaseRequest->comparison?->selected_supplier_quotation_id;
        $this->selection_reason = $purchaseRequest->comparison?->selection_reason ?? '';
        $this->generated_purchase_order_id = PurchaseOrder::query()
            ->whereHas('quotation', fn ($query) => $query->where('requirement_id', $purchaseRequest->id))
            ->value('id');
    }

    public function openWinnerModal(): void
    {
        abort_unless(auth()->user()->can('purchases.aprobar'), 403);

        $this->purchaseRequest->load(['comparison.selectedQuotation.supplier']);
        $this->selected_supplier_quotation_id = $this->purchaseRequest->comparison?->selected_supplier_quotation_id;
        $this->selection_reason = $this->purchaseRequest->comparison?->selection_reason ?? '';
        $this->showWinnerModal = true;
    }

    public function closeWinnerModal(): void
    {
        $this->showWinnerModal = false;
        $this->resetValidation(['selected_supplier_quotation_id', 'selection_reason']);
    }

    public function saveSelection(UpsertQuotationComparison $upsertQuotationComparison): void
    {
        abort_unless(auth()->user()->can('purchases.aprobar'), 403);

        $validated = $this->validate([
            'selected_supplier_quotation_id' => ['required', 'integer', 'exists:supplier_quotations,id'],
            'selection_reason' => ['required', 'string'],
        ], [], [
            'selected_supplier_quotation_id' => 'cotización ganadora',
            'selection_reason' => 'motivo de selección',
        ]);

        $quotation = SupplierQuotation::query()
            ->whereBelongsTo($this->purchaseRequest)
            ->findOrFail($validated['selected_supplier_quotation_id']);

        $upsertQuotationComparison->handle(
            $this->purchaseRequest,
            $quotation,
            auth()->user(),
            $validated['selection_reason'],
        );

        $this->purchaseRequest->refresh();
        $this->showWinnerModal = false;
        $this->successToast('Proveedor ganador seleccionado correctamente.');

        $this->redirectRoute('modules.purchases', navigate: true);
    }

    public function generateOrder(
        GeneratePurchaseOrder $generatePurchaseOrder,
        UpsertQuotationComparison $upsertQuotationComparison,
    ): void {
        abort_unless(auth()->user()->can('purchases.aprobar'), 403);

        $validated = $this->validate([
            'selected_supplier_quotation_id' => ['required', 'integer', 'exists:supplier_quotations,id'],
            'selection_reason' => ['required', 'string'],
        ], [], [
            'selected_supplier_quotation_id' => 'cotización ganadora',
            'selection_reason' => 'motivo de selección',
        ]);

        $quotation = SupplierQuotation::query()
            ->whereBelongsTo($this->purchaseRequest)
            ->findOrFail($validated['selected_supplier_quotation_id']);

        $comparison = $this->purchaseRequest->comparison;

        if (
            $comparison === null
            || (int) $comparison->selected_supplier_quotation_id !== $quotation->id
        ) {
            $upsertQuotationComparison->handle(
                $this->purchaseRequest,
                $quotation,
                auth()->user(),
                $validated['selection_reason'],
            );

            $this->purchaseRequest->refresh();
        }

        $purchaseOrder = $generatePurchaseOrder->handle($this->purchaseRequest);
        $this->generated_purchase_order_id = $purchaseOrder->id;
        $this->purchaseRequest->refresh();

        $this->showWinnerModal = false;
        $this->successToast('Orden generada correctamente.');

        $this->redirectRoute('purchases.orders', navigate: true);
    }
}
