<?php

namespace App\Actions\Purchases;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;

class CancelPurchaseOrder
{
    public function handle(PurchaseOrder $purchaseOrder, User $user, string $reason): PurchaseOrder
    {
        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Cancelled->value(),
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return $purchaseOrder->refresh();
    }
}
