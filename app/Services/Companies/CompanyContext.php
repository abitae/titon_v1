<?php

namespace App\Services\Companies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;

class CompanyContext
{
    public const SESSION_KEY = 'active_company_id';

    public function __construct(
        protected PermissionRegistrar $permissionRegistrar,
    ) {}

    /**
     * @return Collection<int, Company>
     */
    public function availableFor(User $user): Collection
    {
        return $user->activeCompanies()
            ->withPivot(['role_id', 'active', 'default_company'])
            ->orderByDesc('company_user.default_company')
            ->orderBy('companies.name')
            ->get();
    }

    public function resolveFor(User $user): ?Company
    {
        $companies = $this->availableFor($user);

        if ($companies->isEmpty()) {
            $this->clear();

            return null;
        }

        $activeCompanyId = session(self::SESSION_KEY);

        /** @var Company|null $company */
        $company = $companies->firstWhere('id', $activeCompanyId)
            ?? $companies->first(fn (Company $company): bool => (bool) $company->pivot?->default_company)
            ?? $companies->first();

        if ($company instanceof Company) {
            $this->remember($company);
        }

        return $company;
    }

    public function remember(Company $company): void
    {
        session([self::SESSION_KEY => $company->getKey()]);
        setPermissionsTeamId($company->getKey());
        $this->permissionRegistrar->setPermissionsTeamId($company->getKey());

        if (auth()->check()) {
            auth()->user()
                ->unsetRelation('roles')
                ->unsetRelation('permissions');
        }
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
        setPermissionsTeamId(null);
        $this->permissionRegistrar->setPermissionsTeamId(null);
    }
}
