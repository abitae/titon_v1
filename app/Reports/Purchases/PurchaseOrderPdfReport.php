<?php

namespace App\Reports\Purchases;

use App\Models\PurchaseRequest;
use App\Services\Pdf\MpdfBuilder;

class PurchaseOrderPdfReport
{
    public function __construct(
        protected MpdfBuilder $mpdfBuilder,
    ) {}

    public function build(PurchaseRequest $purchaseRequest): string
    {
        $comparison = $purchaseRequest->comparison;
        $quotation = $comparison?->selectedQuotation;

        return $this->mpdfBuilder->buildFromView('reports.pdf.purchases.purchase-order-preview', [
            'purchaseRequest' => $purchaseRequest,
            'comparison' => $comparison,
            'quotation' => $quotation,
        ], 'Orden de compra');
    }
}
