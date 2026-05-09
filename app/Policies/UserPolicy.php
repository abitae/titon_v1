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
        return $user->can('users.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('users.crear');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.editar');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('users.eliminar') && $user->isNot($model);
    }
}
