<?php

namespace App\Services\Codes;

use App\Enums\CorrelativeSubject;
use App\Enums\OrderType;
use App\Models\Company;
use App\Models\Project;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;

class CodeGeneratorService
{
    public function __construct(
        protected IssueCompanyCorrelativeCode $issuer,
    ) {}

    public function peek(Company $company, Project $project, CorrelativeSubject $subject, ?OrderType $orderType = null, ?int $year = null): string
    {
        return $this->issuer->peek(
            $company,
            $subject,
            $this->series($project, $subject, $orderType),
            $year,
            $this->suffixOverride($subject, $orderType),
        );
    }

    public function generate(Company $company, Project $project, CorrelativeSubject $subject, ?OrderType $orderType = null, ?int $year = null): string
    {
        return $this->issuer->issue(
            $company,
            $subject,
            $this->series($project, $subject, $orderType),
            $year,
            $this->suffixOverride($subject, $orderType),
        );
    }

    protected function series(Project $project, CorrelativeSubject $subject, ?OrderType $orderType = null): string
    {
        $base = $this->resolveProjectSeries($project);

        if ($orderType !== null && in_array($subject, [CorrelativeSubject::Order, CorrelativeSubject::PurchaseOrder], true)) {
            return $base.'-'.$orderType->suffix();
        }

        return $base;
    }

    /**
     * Serie de obra para correlativos operativos (máx. 16 caracteres en BD).
     * Ejemplo: OBR001 → TITON-OBR001-REQ-2026-000001
     */
    protected function resolveProjectSeries(Project $project): string
    {
        $code = trim((string) ($project->code ?? ''));

        if ($code !== '' && strlen($code) <= 16 && ! str_contains($code, '-')) {
            return mb_strtoupper($code);
        }

        return 'OBR'.str_pad((string) $project->id, 3, '0', STR_PAD_LEFT);
    }

    protected function suffixOverride(CorrelativeSubject $subject, ?OrderType $orderType): ?string
    {
        if ($subject !== CorrelativeSubject::Order && $subject !== CorrelativeSubject::PurchaseOrder) {
            return null;
        }

        return $orderType?->suffix() ?? OrderType::Purchase->suffix();
    }
}
