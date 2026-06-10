<?php

namespace App\Concerns;

trait ViewsPdfInModal
{
    public bool $showPdfModal = false;

    public string $pdfViewerUrl = '';

    public string $pdfViewerTitle = '';

    public string $pdfViewerSubtitle = 'Vista previa del documento PDF';

    public function openPdfModal(string $url, string $title, string $subtitle = 'Vista previa del documento PDF'): void
    {
        $this->pdfViewerUrl = $this->appendPdfPreviewQuery($url);
        $this->pdfViewerTitle = $title;
        $this->pdfViewerSubtitle = $subtitle;
        $this->showPdfModal = true;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function openRoutePdfModal(string $routeName, string $title, array $params = [], string $subtitle = 'Vista previa del reporte PDF'): void
    {
        $this->openPdfModal(route($routeName, $params, absolute: false), $title, $subtitle);
    }

    public function closePdfModal(): void
    {
        $this->showPdfModal = false;
        $this->pdfViewerUrl = '';
        $this->pdfViewerTitle = '';
        $this->pdfViewerSubtitle = 'Vista previa del documento PDF';
    }

    protected function appendPdfPreviewQuery(string $url): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.'preview=1';
    }
}
