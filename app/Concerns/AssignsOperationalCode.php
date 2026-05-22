<?php

namespace App\Concerns;

use App\Enums\CorrelativeSubject;
use App\Enums\OrderType;
use App\Models\Company;
use App\Models\Project;
use App\Services\Codes\CodeGeneratorService;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;

trait AssignsOperationalCode
{
    protected function assignOperationalCode(
        Company $company,
        CorrelativeSubject $subject,
        ?Project $project = null,
        ?OrderType $orderType = null,
        ?string $existingCode = null,
        bool $isEditing = false,
    ): string {
        if ($isEditing && $existingCode !== null && $existingCode !== '') {
            return $existingCode;
        }

        if ($project !== null) {
            return app(CodeGeneratorService::class)->generate($company, $project, $subject, $orderType);
        }

        return app(IssueCompanyCorrelativeCode::class)->issue($company, $subject);
    }
}
