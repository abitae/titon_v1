<?php

namespace App\Actions\Purchases;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class ApprovePurchaseOrder
{
    public function handle(Order $order, User $user, ?string $notes = null): Order
    {
        $order->update([
            'status' => OrderStatus::InAttention->value(),
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        return $order->refresh();
    }
}
