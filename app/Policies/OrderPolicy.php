<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('purchases.ver');
    }

    public function view(User $user, Order $order): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('purchases.aprobar');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can('purchases.editar') || $user->can('purchases.aprobar');
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can('purchases.eliminar');
    }

    public function export(User $user, Order $order): bool
    {
        return $user->can('purchases.exportar');
    }
}
