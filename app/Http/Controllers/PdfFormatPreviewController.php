<?php

namespace App\Http\Controllers;

use App\Reports\Pdf\PdfFormatPreviewReport;
use Illuminate\Http\Response;

class PdfFormatPreviewController extends Controller
{
    public function __invoke(PdfFormatPreviewReport $pdfFormatPreviewReport): Response
    {
        abort_unless(auth()->user()?->can('pdf-formats.ver'), 403);

        $pdf = $pdfFormatPreviewReport->build(auth()->user());

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="vista-previa-formato-pdf.pdf"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
