<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Database\Factories\WarehouseMovementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class WarehouseMovement extends Model implements Auditable
{
    /** @use HasFactory<WarehouseMovementFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'warehouse_stock_item_id',
        'warehouse_transfer_id',
        'movement_code',
        'direction',
        'source',
        'order_id',
        'order_item_id',
        'order_conformity_id',
        'responsible_user_id',
        'movement_date',
        'quantity',
        'unit_cost',
        'total_amount',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'movement_date' => 'date',
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'total_amount' => 'decimal:2',
        ];
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(WarehouseStockItem::class, 'warehouse_stock_item_id');
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(WarehouseTransfer::class, 'warehouse_transfer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function orderConformity(): BelongsTo
    {
        return $this->belongsTo(OrderConformity::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }
}
