<?php

namespace App\Actions\Contracts;

use App\Enums\SupplierContractStatus;
use App\Models\SupplierContract;
use App\Models\User;

class ApproveSupplierContract
{
    public function handle(SupplierContract $supplierContract, User $user, ?string $notes = null): SupplierContract
    {
        $supplierContract->update([
            'status' => SupplierContractStatus::Approved->value(),
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        return $supplierContract->refresh();
    }
}
