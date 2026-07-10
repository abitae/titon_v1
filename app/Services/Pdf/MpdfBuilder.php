<?php

namespace App\Services\Pdf;

use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class MpdfBuilder implements PdfReportBuilder
{
    public function __construct(
        protected Factory $viewFactory,
        protected PdfBrandingResolver $brandingResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function buildFromView(string $view, array $data = [], string $title = 'Reporte', ?User $actor = null, ?PdfBrandingData $branding = null): string
    {
        $branding ??= $this->brandingResolver->resolve($actor);

        return $this->buildHtml(
            $this->viewFactory->make($view, [
                ...$data,
                'pdfBranding' => $branding,
            ])->render(),
            $title,
            $branding,
        );
    }

    public function buildHtml(string $html, string $title = 'Reporte', ?PdfBrandingData $branding = null): string
    {
        $branding ??= $this->brandingResolver->resolve();

        $tempDir = storage_path('app/mpdf-temp');

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => $branding->marginLeft,
            'margin_right' => $branding->marginRight,
            'margin_top' => $branding->marginTop,
            'margin_bottom' => $branding->marginBottom,
            'tempDir' => $tempDir,
        ]);

        $mpdf->SetTitle($title);
        $mpdf->SetAuthor($branding->displayTitle());
        $mpdf->SetCreator(config('app.name'));
        $mpdf->SetDisplayMode('fullpage');

        if ($branding->hasHeader()) {
            $mpdf->SetHTMLHeader(
                $this->viewFactory->make('reports.pdf.partials.mpdf-header', [
                    'branding' => $branding,
                ])->render(),
            );
        }

        $mpdf->SetHTMLFooter(
            $this->viewFactory->make('reports.pdf.partials.mpdf-footer', [
                'branding' => $branding,
                'title' => $title,
            ])->render(),
        );

        $mpdf->WriteHTML($html);

        return $mpdf->Output(Str::slug($title).'.pdf', Destination::STRING_RETURN);
    }

    public function mergePdfStringWithFile(string $primaryPdf, string $appendPdfPath): string
    {
        if (! is_readable($appendPdfPath)) {
            return $primaryPdf;
        }

        $tempDir = storage_path('app/mpdf-temp');

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $primaryPath = $tempDir.'/merge-primary-'.uniqid('', true).'.pdf';
        file_put_contents($primaryPath, $primaryPdf);

        try {
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'tempDir' => $tempDir,
            ]);

            $this->importPdfPages($mpdf, $primaryPath);
            $this->importPdfPages($mpdf, $appendPdfPath);

            return $mpdf->Output('merged.pdf', Destination::STRING_RETURN);
        } finally {
            if (is_file($primaryPath)) {
                unlink($primaryPath);
            }
        }
    }

    protected function importPdfPages(Mpdf $mpdf, string $path): void
    {
        $pageCount = $mpdf->SetSourceFile($path);

        for ($page = 1; $page <= $pageCount; $page++) {
            $templateId = $mpdf->ImportPage($page);
            $size = $mpdf->getTemplateSize($templateId);
            $mpdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $mpdf->UseTemplate($templateId);
        }
    }
}
