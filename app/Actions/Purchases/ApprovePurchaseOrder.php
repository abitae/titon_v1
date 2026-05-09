<?php

namespace App\Actions\Purchases;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;

class ApprovePurchaseOrder
{
    public function handle(PurchaseOrder $purchaseOrder, User $user, ?string $notes = null): PurchaseOrder
    {
        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Approved->value(),
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        return $purchaseOrder->refresh();
    }
}
