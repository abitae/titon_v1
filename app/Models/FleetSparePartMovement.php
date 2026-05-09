<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Database\Factories\FleetSparePartMovementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class FleetSparePartMovement extends Model implements Auditable
{
    /** @use HasFactory<FleetSparePartMovementFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory;

    protected $table = 'fleet_spare_part_movements';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'fleet_spare_part_id',
        'movement_code',
        'fleet_work_order_id',
        'created_by_user_id',
        'direction',
        'quantity',
        'unit_cost',
        'total_amount',
        'reference',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<FleetSparePart, $this>
     */
    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(FleetSparePart::class, 'fleet_spare_part_id');
    }

    /**
     * @return BelongsTo<FleetWorkOrder, $this>
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(FleetWorkOrder::class, 'fleet_work_order_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
