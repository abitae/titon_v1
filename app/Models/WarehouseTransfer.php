<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Database\Factories\WarehouseTransferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class WarehouseTransfer extends Model implements Auditable
{
    /** @use HasFactory<WarehouseTransferFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'transfer_code',
        'source_work_project_id',
        'destination_work_project_id',
        'warehouse_stock_item_id',
        'responsible_user_id',
        'transfer_date',
        'quantity',
        'unit_cost',
        'total_amount',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'transfer_date' => 'date',
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'total_amount' => 'decimal:2',
        ];
    }

    public function sourceProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'source_work_project_id');
    }

    public function destinationProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'destination_work_project_id');
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(WarehouseStockItem::class, 'warehouse_stock_item_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(WarehouseMovement::class);
    }
}
