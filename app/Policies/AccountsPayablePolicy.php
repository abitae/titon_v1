<?php

namespace App\Policies;

use App\Models\AccountsPayable;
use App\Models\User;

class AccountsPayablePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cuentas_pagar.ver') || $user->can('payments.ver');
    }

    public function view(User $user, AccountsPayable $accountsPayable): bool
    {
        return $this->viewAny($user);
    }

    public function uploadDocuments(User $user, AccountsPayable $accountsPayable): bool
    {
        return $user->can('cuentas_pagar.subir_documentos');
    }

    public function pay(User $user, AccountsPayable $accountsPayable): bool
    {
        return $user->can('cuentas_pagar.pagar');
    }

    public function export(User $user, AccountsPayable $accountsPayable): bool
    {
        return $user->can('cuentas_pagar.exportar');
    }
}
