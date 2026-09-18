<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.ver');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.ver') && ! $model->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->can('users.crear');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.editar') && ! $model->isSuperAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('users.eliminar') && $user->isNot($model) && ! $model->isSuperAdmin();
    }
}
