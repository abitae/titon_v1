<?php

namespace App\Policies;

use App\Enums\RequirementStatus;
use App\Models\Requirement;
use App\Models\User;

class RequirementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('requerimientos.ver') || $user->can('purchases.ver');
    }

    public function view(User $user, Requirement $requirement): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('requerimientos.crear') || $user->can('purchases.crear');
    }

    public function update(User $user, Requirement $requirement): bool
    {
        if ($requirement->status === RequirementStatus::Cancelled->value()) {
            return false;
        }

        return $user->can('requerimientos.editar') || $user->can('purchases.editar');
    }

    public function delete(User $user, Requirement $requirement): bool
    {
        return $user->can('requerimientos.cancelar') || $user->can('purchases.eliminar');
    }

    public function sendToSuppliers(User $user, Requirement $requirement): bool
    {
        return $user->can('requerimientos.enviar_proveedor') || $user->can('purchases.aprobar');
    }
}
