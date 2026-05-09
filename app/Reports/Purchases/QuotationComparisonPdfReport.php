<?php

namespace App\Reports\Purchases;

use App\Models\PurchaseRequest;
use App\Services\Pdf\MpdfBuilder;
use App\Services\Purchases\QuotationComparisonSummary;

class QuotationComparisonPdfReport
{
    public function __construct(
        protected QuotationComparisonSummary $quotationComparisonSummary,
        protected MpdfBuilder $mpdfBuilder,
    ) {}

    public function build(PurchaseRequest $purchaseRequest): string
    {
        $summary = $this->quotationComparisonSummary->build($purchaseRequest);

        return $this->mpdfBuilder->buildFromView('reports.pdf.purchases.quotation-comparison', [
            'purchaseRequest' => $purchaseRequest,
            'summary' => $summary,
        ], 'Comparativa de cotizaciones');
    }
}
