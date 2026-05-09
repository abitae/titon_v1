<?php

namespace App\Actions\Purchases;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;

class ObservePurchaseOrder
{
    public function handle(PurchaseOrder $purchaseOrder, string $observation): PurchaseOrder
    {
        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Observed->value(),
            'observation' => $observation,
        ]);

        return $purchaseOrder->refresh();
    }
}
