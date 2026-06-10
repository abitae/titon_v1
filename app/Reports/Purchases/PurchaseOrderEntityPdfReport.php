<?php

namespace App\Reports\Purchases;

use App\Models\PurchaseOrder;
use App\Services\Pdf\MpdfBuilder;

class PurchaseOrderEntityPdfReport
{
    public function __construct(
        protected MpdfBuilder $mpdfBuilder,
    ) {}

    public function build(PurchaseOrder $purchaseOrder): string
    {
        $orderPdf = $this->mpdfBuilder->buildFromView('reports.pdf.purchases.purchase-order-entity', [
            'purchaseOrder' => $purchaseOrder,
        ], 'Orden de compra');

        $attachedQuotation = $purchaseOrder->getFirstMedia('cotizacion_adjunta');

        if ($attachedQuotation === null) {
            return $orderPdf;
        }

        return $this->mpdfBuilder->mergePdfStringWithFile($orderPdf, $attachedQuotation->getPath());
    }
}
