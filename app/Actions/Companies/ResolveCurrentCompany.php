<?php

namespace App\Actions\Companies;

use App\Models\Company;
use App\Models\User;
use App\Services\Companies\CompanyContext;

class ResolveCurrentCompany
{
    public function __construct(
        protected CompanyContext $companyContext,
    ) {}

    public function handle(?User $user): ?Company
    {
        return $user instanceof User
            ? $this->companyContext->resolveFor($user)
            : null;
    }
}
