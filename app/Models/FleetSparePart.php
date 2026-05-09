<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Database\Factories\FleetSparePartFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class FleetSparePart extends Model implements Auditable
{
    /** @use HasFactory<FleetSparePartFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory;

    protected $table = 'fleet_spare_parts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'supplier_id',
        'warehouse_project_id',
        'code',
        'name',
        'category',
        'unit',
        'stock_quantity',
        'min_stock',
        'unit_cost',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stock_quantity' => 'decimal:3',
            'min_stock' => 'decimal:3',
            'unit_cost' => 'decimal:4',
        ];
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function warehouseProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'warehouse_project_id');
    }

    /**
     * @return HasMany<FleetSparePartMovement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(FleetSparePartMovement::class);
    }

    public function isBelowMinStock(): bool
    {
        return bccomp((string) $this->stock_quantity, (string) $this->min_stock, 3) < 1;
    }
}
