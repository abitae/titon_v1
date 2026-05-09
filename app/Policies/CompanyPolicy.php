<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('companies.ver');
    }

    public function view(User $user, Company $company): bool
    {
        return $user->can('companies.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('companies.crear');
    }

    public function update(User $user, Company $company): bool
    {
        return $user->can('companies.editar');
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->can('companies.eliminar');
    }
}
