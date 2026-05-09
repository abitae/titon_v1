<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Database\Factories\FleetPreventiveMaintenanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class FleetPreventiveMaintenance extends Model implements Auditable
{
    /** @use HasFactory<FleetPreventiveMaintenanceFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory;

    protected $table = 'fleet_preventive_maintenances';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'fleet_equipment_id',
        'code',
        'responsible_user_id',
        'maintenance_type',
        'scheduled_date',
        'scheduled_odometer',
        'scheduled_hour_meter',
        'priority',
        'status',
        'cost',
        'observations',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'scheduled_odometer' => 'decimal:2',
            'scheduled_hour_meter' => 'decimal:2',
            'cost' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<FleetEquipment, $this>
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(FleetEquipment::class, 'fleet_equipment_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * @return HasMany<FleetWorkOrder, $this>
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(FleetWorkOrder::class, 'fleet_preventive_maintenance_id');
    }
}
