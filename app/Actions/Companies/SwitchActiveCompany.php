<?php

namespace App\Actions\Companies;

use App\Models\Company;
use App\Models\User;
use App\Services\Audit\UserAuditLogger;
use App\Services\Companies\CompanyContext;

class SwitchActiveCompany
{
    public function __construct(
        protected CompanyContext $companyContext,
        protected UserAuditLogger $userAuditLogger,
    ) {}

    public function handle(User $user, Company $company): Company
    {
        $previousCompany = $this->companyContext->resolveFor($user);

        abort_unless(
            $user->activeCompanies()->whereKey($company->getKey())->exists(),
            403,
        );

        $this->companyContext->remember($company);

        $this->userAuditLogger->log(
            action: 'cambio_empresa_activa',
            module: 'Seguridad',
            auditable: $company,
            oldValues: ['empresa_anterior' => $previousCompany?->name],
            newValues: ['empresa_activa' => $company->name],
            observation: 'Cambio de empresa activa.',
            actor: $user,
            companyId: $company->id,
        );

        return $company;
    }
}
