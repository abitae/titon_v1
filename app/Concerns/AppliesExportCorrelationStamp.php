<?php

namespace App\Concerns;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Enums\CorrelativeSubject;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;

trait AppliesExportCorrelationStamp
{
    protected function stampedExportFilename(string $fallbackWithExtension): string
    {
        $user = auth()->user();

        if ($user === null) {
            return $fallbackWithExtension;
        }

        $company = app(ResolveCurrentCompany::class)->handle($user);

        if ($company === null) {
            return $fallbackWithExtension;
        }

        $code = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::ExportedReport);
        $stamp = (string) preg_replace('/[^a-zA-Z0-9._-]+/', '-', $code);
        $parts = pathinfo($fallbackWithExtension);

        return "{$stamp}_{$parts['filename']}".(isset($parts['extension']) ? '.'.$parts['extension'] : '');
    }
}
