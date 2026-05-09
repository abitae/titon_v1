<?php

namespace App\Reports\Mechanics;

use App\Models\User;
use App\Services\Pdf\MpdfBuilder;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;

class GenericMechanicsPdfReport
{
    public function __construct(
        protected MpdfBuilder $mpdfBuilder,
        protected Factory $viewFactory,
    ) {}

    /**
     * @param  list<string>  $headings
     * @param  Collection<int, array<int|string, mixed>>  $rows
     * @param  list<string>  $summaryLines
     */
    public function build(
        User $actor,
        string $pdfDocumentTitle,
        string $headingTitle,
        array $headings,
        Collection $rows,
        array $summaryLines = [],
    ): string {
        $html = $this->viewFactory->make('reports.mechanics.generic-table-pdf', [
            'generatedAt' => now(),
            'actor' => $actor,
            'headingTitle' => $headingTitle,
            'headings' => $headings,
            'rows' => $rows,
            'summaryLines' => $summaryLines,
        ])->render();

        return $this->mpdfBuilder->buildHtml($html, $pdfDocumentTitle);
    }
}
