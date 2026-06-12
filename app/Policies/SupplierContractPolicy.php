<?php

namespace App\Policies;

use App\Models\SupplierContract;
use App\Models\User;

class SupplierContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('contracts.ver');
    }

    public function view(User $user, SupplierContract $supplierContract): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('contracts.crear');
    }

    public function update(User $user, SupplierContract $supplierContract): bool
    {
        return $user->can('contracts.editar');
    }

    public function approve(User $user, SupplierContract $supplierContract): bool
    {
        return $user->can('contracts.aprobar');
    }

    public function export(User $user, SupplierContract $supplierContract): bool
    {
        return $user->can('contracts.exportar');
    }
}
