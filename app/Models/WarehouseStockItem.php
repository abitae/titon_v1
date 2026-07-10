<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use App\Support\Decimal;
use Database\Factories\WarehouseStockItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class WarehouseStockItem extends Model implements Auditable
{
    /** @use HasFactory<WarehouseStockItemFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'work_project_id',
        'supplier_id',
        'item_type',
        'description',
        'unit',
        'stock_quantity',
        'unit_cost',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'decimal:3',
            'unit_cost' => 'decimal:4',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'work_project_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(WarehouseMovement::class);
    }

    public function isMaterial(): bool
    {
        return $this->item_type === 'material';
    }

    public function hasAvailableStock(string $quantity): bool
    {
        return Decimal::compare($this->stock_quantity, $quantity, 3) >= 0;
    }
}
