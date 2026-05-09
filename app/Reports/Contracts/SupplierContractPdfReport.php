<?php

namespace App\Reports\Contracts;

use App\Models\SupplierContract;
use App\Services\Pdf\MpdfBuilder;

class SupplierContractPdfReport
{
    public function __construct(
        protected MpdfBuilder $mpdfBuilder,
    ) {}

    public function build(SupplierContract $supplierContract): string
    {
        return $this->mpdfBuilder->buildFromView('reports.pdf.contracts.supplier-contract', [
            'supplierContract' => $supplierContract,
        ], 'Contrato de proveedor');
    }
}
