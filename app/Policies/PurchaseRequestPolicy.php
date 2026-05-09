<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;

class PurchaseRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('purchases.ver');
    }

    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->can('purchases.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('purchases.crear');
    }

    public function update(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->can('purchases.editar');
    }

    public function delete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->can('purchases.eliminar');
    }
}
