<?php

namespace App\Services\Documents;

use App\Enums\CorrelativeSubject;
use App\Models\Company;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;

class GenerateDocumentCode
{
    /**
     * Genera vista previa del siguiente código interno para documentos operativos.
     */
    public function handle(int $companyId): string
    {
        $company = Company::query()->findOrFail($companyId);

        return app(IssueCompanyCorrelativeCode::class)->peek($company, CorrelativeSubject::Document);
    }
}
