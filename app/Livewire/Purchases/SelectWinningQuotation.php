<?php

namespace App\Livewire\Purchases;

use App\Actions\Purchases\GeneratePurchaseOrder;
use App\Actions\Purchases\UpsertQuotationComparison;
use App\Concerns\InteractsWithToast;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\SupplierQuotation;
use App\Services\Purchases\QuotationComparisonSummary;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SelectWinningQuotation extends Component
{
    use InteractsWithToast;

    public string $title = 'Seleccion de ganador';

    public PurchaseRequest $purchaseRequest;

    public ?int $selected_supplier_quotation_id = null;

    public string $selection_reason = '';

    public ?int $generated_purchase_order_id = null;

    public function mount(PurchaseRequest $purchaseRequest): void
    {
        $this->purchaseRequest = $purchaseRequest->load(['project', 'comparison']);
        $this->selected_supplier_quotation_id = $this->purchaseRequest->comparison?->selected_supplier_quotation_id;
        $this->selection_reason = $this->purchaseRequest->comparison?->selection_reason ?? '';
        $this->generated_purchase_order_id = PurchaseOrder::query()
            ->whereHas('quotation', fn ($query) => $query->where('requirement_id', $purchaseRequest->id))
            ->value('id');
    }

    public function render(QuotationComparisonSummary $quotationComparisonSummary): View
    {
        return view('livewire.purchases.select-winning-quotation', [
            'summary' => $quotationComparisonSummary->build($this->purchaseRequest),
            'comparison' => $this->purchaseRequest->comparison()->with(['selectedQuotation.supplier', 'selectedByUser'])->first(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function saveSelection(UpsertQuotationComparison $upsertQuotationComparison): void
    {
        abort_unless(auth()->user()->can('purchases.aprobar'), 403);

        $validated = $this->validate([
            'selected_supplier_quotation_id' => ['required', 'integer', 'exists:supplier_quotations,id'],
            'selection_reason' => ['required', 'string'],
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
        $this->successToast('Proveedor ganador seleccionado correctamente.');
    }

    public function generateOrder(GeneratePurchaseOrder $generatePurchaseOrder): void
    {
        abort_unless(auth()->user()->can('purchases.aprobar'), 403);

        $purchaseOrder = $generatePurchaseOrder->handle($this->purchaseRequest);
        $this->generated_purchase_order_id = $purchaseOrder->id;
        $this->purchaseRequest->refresh();

        $this->successToast('Orden generada correctamente.');
    }
}
