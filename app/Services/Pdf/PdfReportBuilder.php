<?php

namespace App\Services\Pdf;

interface PdfReportBuilder
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function buildFromView(string $view, array $data = [], string $title = 'Reporte'): string;

    public function buildHtml(string $html, string $title = 'Reporte'): string;
}
