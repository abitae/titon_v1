<?php

namespace App\Livewire\Purchases;

use App\Concerns\InteractsWithToast;
use App\Concerns\SelectsWinningQuotation;
use App\Models\PurchaseRequest;
use App\Services\Purchases\QuotationComparisonSummary;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ShowQuotationComparison extends Component
{
    use InteractsWithToast, SelectsWinningQuotation;

    public string $title = 'Comparativa visual';

    public PurchaseRequest $purchaseRequest;

    public function mount(PurchaseRequest $purchaseRequest): void
    {
        $this->purchaseRequest = $purchaseRequest->load(['project', 'comparison.selectedQuotation.supplier']);
        $this->bootWinningQuotationSelection($this->purchaseRequest);

        if (request()->boolean('selectWinner')) {
            $this->openWinnerModal();
        }
    }

    public function render(QuotationComparisonSummary $quotationComparisonSummary): View
    {
        return view('livewire.purchases.show-quotation-comparison', [
            'summary' => $quotationComparisonSummary->build($this->purchaseRequest),
            'comparison' => $this->purchaseRequest->comparison()->with(['selectedQuotation.supplier', 'selectedByUser'])->first(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }
}
