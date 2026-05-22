<?php

namespace App\Actions\Purchases;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class CancelPurchaseOrder
{
    public function handle(Order $order, User $user, string $reason): Order
    {
        $order->update([
            'status' => OrderStatus::Cancelled->value(),
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return $order->refresh();
    }
}
