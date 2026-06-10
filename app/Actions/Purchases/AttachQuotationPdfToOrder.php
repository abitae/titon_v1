<?php

namespace App\Actions\Purchases;

use App\Models\Order;
use App\Models\SupplierQuotation;

class AttachQuotationPdfToOrder
{
    public function handle(Order $order, SupplierQuotation $quotation): void
    {
        if (! $quotation->isPdfCapture()) {
            $order->clearMediaCollection('cotizacion_adjunta');

            return;
        }

        $quotationPdf = $quotation->getFirstMedia('cotizacion_pdf');

        if ($quotationPdf === null) {
            return;
        }

        $existing = $order->getFirstMedia('cotizacion_adjunta');

        if (
            $existing !== null
            && (int) $existing->getCustomProperty('source_media_id') === $quotationPdf->id
        ) {
            return;
        }

        $quotationPdfPath = $quotationPdf->getPath();

        if (! is_file($quotationPdfPath)) {
            return;
        }

        $order->clearMediaCollection('cotizacion_adjunta');

        $order->addMedia($quotationPdfPath)
            ->usingName($quotationPdf->name)
            ->usingFileName($quotationPdf->file_name)
            ->withCustomProperties([
                'source_media_id' => $quotationPdf->id,
                'source_quotation_id' => $quotation->id,
            ])
            ->toMediaCollection('cotizacion_adjunta', $quotationPdf->disk);
    }
}
