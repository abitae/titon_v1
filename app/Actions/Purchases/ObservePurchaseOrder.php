<?php

namespace App\Actions\Purchases;

use App\Enums\OrderStatus;
use App\Models\Order;

class ObservePurchaseOrder
{
    public function handle(Order $order, string $observation): Order
    {
        $order->update([
            'status' => OrderStatus::InAttention->value(),
            'observation' => $observation,
        ]);

        return $order->refresh();
    }
}
