<?php

namespace App\Services\Purchases;

use App\Models\PurchaseRequest;
use App\Models\SupplierQuotation;
use Illuminate\Support\Collection;

class QuotationComparisonSummary
{
    /**
     * @return array{min_total: float|null, min_delivery_time: int|null, quotations: Collection<int, SupplierQuotation>}
     */
    public function build(PurchaseRequest $purchaseRequest): array
    {
        $quotations = $purchaseRequest->quotations()
            ->with(['supplier', 'items'])
            ->orderBy('total')
            ->get();

        return [
            'min_total' => $quotations->min(fn (SupplierQuotation $quotation): float => (float) $quotation->total),
            'min_delivery_time' => $quotations->min(fn (SupplierQuotation $quotation): int => (int) $quotation->delivery_time),
            'quotations' => $quotations,
        ];
    }
}
