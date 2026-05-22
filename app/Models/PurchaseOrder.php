<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/** @deprecated Use Order */
class PurchaseOrder extends Order
{
    protected $table = 'orders';

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
