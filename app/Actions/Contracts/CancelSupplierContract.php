<?php

namespace App\Actions\Contracts;

use App\Enums\SupplierContractStatus;
use App\Models\SupplierContract;
use App\Models\User;

class CancelSupplierContract
{
    public function handle(SupplierContract $supplierContract, User $user, string $reason): SupplierContract
    {
        $supplierContract->update([
            'status' => SupplierContractStatus::Cancelled->value(),
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return $supplierContract->refresh();
    }
}
