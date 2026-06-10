<?php

namespace App\Concerns;

use App\Models\SupplierQuotation;

trait ViewsQuotationPdf
{
    public bool $showPdfModal = false;

    public string $pdfViewerUrl = '';

    public string $pdfViewerTitle = '';

    public function openPdfModal(int $quotationId): void
    {
        $quotation = SupplierQuotation::query()
            ->whereBelongsTo($this->purchaseRequest)
            ->with('supplier')
            ->findOrFail($quotationId);

        $url = $quotation->quotationPdfPreviewUrl();

        $this->pdfViewerUrl = $url;
        $this->pdfViewerTitle = ($quotation->supplier?->business_name ?? 'Cotización').' · '.$quotation->code;
        $this->showPdfModal = true;
    }

    public function closePdfModal(): void
    {
        $this->showPdfModal = false;
        $this->pdfViewerUrl = '';
        $this->pdfViewerTitle = '';
    }
}
