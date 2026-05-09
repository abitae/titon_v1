<?php

namespace App\Policies;

use App\Models\SupplierPayment;
use App\Models\User;

class SupplierPaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('payments.ver');
    }

    public function view(User $user, SupplierPayment $supplierPayment): bool
    {
        return $user->can('payments.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('payments.crear');
    }

    public function update(User $user, SupplierPayment $supplierPayment): bool
    {
        return $user->can('payments.editar');
    }

    public function delete(User $user, SupplierPayment $supplierPayment): bool
    {
        return $user->can('payments.eliminar');
    }
}
