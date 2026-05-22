<?php

namespace App\Models;

use App\Concerns\BelongsToActiveCompany;
use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use BelongsToActiveCompany, HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'company_id',
        'work_project_id',
        'order_id',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'total',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
