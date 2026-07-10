<?php

namespace App\Reports\Mechanics;

use App\Models\User;
use App\Services\Pdf\MpdfBuilder;
use Illuminate\Support\Collection;

class GenericMechanicsPdfReport
{
    public function __construct(
        protected MpdfBuilder $mpdfBuilder,
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
        return $this->mpdfBuilder->buildFromView('reports.mechanics.generic-table-pdf', [
            'generatedAt' => now(),
            'actor' => $actor,
            'headingTitle' => $headingTitle,
            'headings' => $headings,
            'rows' => $rows,
            'summaryLines' => $summaryLines,
        ], $pdfDocumentTitle, $actor);
    }
}
