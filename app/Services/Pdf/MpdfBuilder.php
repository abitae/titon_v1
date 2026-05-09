<?php

namespace App\Services\Pdf;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class MpdfBuilder
{
    public function __construct(
        protected Factory $viewFactory,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function buildFromView(string $view, array $data = [], string $title = 'Reporte'): string
    {
        return $this->buildHtml(
            $this->viewFactory->make($view, $data)->render(),
            $title,
        );
    }

    public function buildHtml(string $html, string $title = 'Reporte'): string
    {
        $tempDir = storage_path('app/mpdf-temp');

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 14,
            'margin_bottom' => 14,
            'tempDir' => $tempDir,
        ]);

        $mpdf->SetTitle($title);
        $mpdf->SetAuthor(config('app.name'));
        $mpdf->SetCreator(config('app.name'));
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->SetHTMLFooter(
            '<div style="border-top:1px solid #cbd5e1;color:#64748b;font-size:10px;padding-top:6px;text-align:right;">'
            .e($title).' | {PAGENO}'
            .'</div>'
        );
        $mpdf->WriteHTML($html);

        return $mpdf->Output(Str::slug($title).'.pdf', Destination::STRING_RETURN);
    }
}
