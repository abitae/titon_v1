<?php

namespace App\Reports\Purchases;

use App\Models\SupplierQuotation;
use App\Services\Pdf\MpdfBuilder;

class SupplierQuotationPdfReport
{
    public function __construct(
        protected MpdfBuilder $mpdfBuilder,
    ) {}

    public function build(SupplierQuotation $quotation): string
    {
        $quotation->loadMissing(['supplier', 'items', 'requirement.project']);

        return $this->mpdfBuilder->buildFromView('reports.pdf.purchases.supplier-quotation', [
            'quotation' => $quotation,
            'purchaseRequest' => $quotation->requirement,
        ], 'Cotizacion '.$quotation->code);
    }
}
