<?php

namespace App\Concerns;

use App\Models\SupplierQuotation;
use App\Services\Purchases\QuotationComparisonSummary;
use Illuminate\Support\Collection;

trait ShowsQuotationComparisonModal
{
    public bool $showComparisonModal = false;

    /**
     * @var list<int>
     */
    public array $comparison_quotation_ids = [];

    public function openComparisonModal(): void
    {
        if (count($this->comparison_quotation_ids) < 2) {
            $this->warningToast('Seleccione al menos 2 cotizaciones para comparar.');

            return;
        }

        $this->showComparisonModal = true;
    }

    public function closeComparisonModal(): void
    {
        $this->showComparisonModal = false;
    }

    /**
     * @return array{min_total: float|null, min_delivery_time: int|null, quotations: Collection<int, SupplierQuotation>}
     */
    public function buildSelectedComparisonSummary(QuotationComparisonSummary $quotationComparisonSummary): array
    {
        $summary = $quotationComparisonSummary->build($this->purchaseRequest);

        $selectedQuotations = $summary['quotations']
            ->whereIn('id', $this->comparison_quotation_ids)
            ->values();

        return [
            'min_total' => $selectedQuotations->min(fn ($quotation): float => (float) $quotation->total),
            'min_delivery_time' => $selectedQuotations->min(fn ($quotation): int => (int) ($quotation->delivery_time_days ?? 0)),
            'quotations' => $selectedQuotations,
        ];
    }
}
