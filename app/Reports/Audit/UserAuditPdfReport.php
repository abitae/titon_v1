<?php

namespace App\Reports\Audit;

use App\Models\Audit;
use App\Services\Pdf\MpdfBuilder;
use Illuminate\Support\Collection;

class UserAuditPdfReport
{
    public function __construct(
        protected MpdfBuilder $mpdfBuilder,
    ) {}

    /**
     * @param  Collection<int, Audit>  $audits
     * @param  array<string, string>  $filters
     */
    public function build(Collection $audits, array $filters): string
    {
        return $this->mpdfBuilder->buildFromView('reports.pdf.audit.user-audits', [
            'audits' => $audits,
            'filters' => $filters,
        ], 'Auditoria de usuarios');
    }
}
