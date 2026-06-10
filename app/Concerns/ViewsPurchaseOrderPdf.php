<?php

namespace App\Concerns;

use App\Models\PurchaseOrder;

trait ViewsPurchaseOrderPdf
{
    public bool $showPdfModal = false;

    public string $pdfViewerUrl = '';

    public string $pdfViewerTitle = '';

    public function openPdfModal(int $purchaseOrderId): void
    {
        $order = PurchaseOrder::query()
            ->with(['supplier', 'project'])
            ->findOrFail($purchaseOrderId);

        $this->pdfViewerUrl = $order->orderPdfPreviewUrl();
        $this->pdfViewerTitle = ($order->supplier?->business_name ?? 'Orden de compra').' · '.$order->code;
        $this->showPdfModal = true;
    }

    public function closePdfModal(): void
    {
        $this->showPdfModal = false;
        $this->pdfViewerUrl = '';
        $this->pdfViewerTitle = '';
    }
}
