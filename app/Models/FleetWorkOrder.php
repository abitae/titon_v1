<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use App\Enums\FleetWorkOrderStatus;
use Carbon\Carbon;
use Database\Factories\FleetWorkOrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FleetWorkOrder extends Model implements Auditable, HasMedia
{
    /** @use HasFactory<FleetWorkOrderFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory, InteractsWithMedia;

    protected $table = 'fleet_work_orders';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'work_project_id',
        'fleet_equipment_id',
        'responsible_user_id',
        'code',
        'type',
        'issued_at',
        'scheduled_date',
        'closed_at',
        'priority',
        'status',
        'work_description',
        'diagnosis',
        'parts_used_description',
        'labor_cost',
        'spare_parts_cost',
        'total_cost',
        'fleet_preventive_maintenance_id',
        'fleet_corrective_maintenance_id',
        'fleet_technical_inspection_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'scheduled_date' => 'date',
            'closed_at' => 'datetime',
            'labor_cost' => 'decimal:2',
            'spare_parts_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    /**
     * Órdenes abiertas con fecha programada vencida.
     *
     * @param  Builder<FleetWorkOrder>  $query
     * @return Builder<FleetWorkOrder>
     */
    public function scopeScheduledOverdue(Builder $query): Builder
    {
        return $query
            ->whereIn('status', FleetWorkOrderStatus::openStatuses())
            ->whereNotNull('scheduled_date')
            ->whereDate('scheduled_date', '<', today());
    }

    public function scheduleRiskLabel(): ?string
    {
        if (! in_array((string) $this->status, FleetWorkOrderStatus::openStatuses(), true) || $this->scheduled_date === null) {
            return null;
        }

        $scheduled = Carbon::parse((string) $this->scheduled_date)->startOfDay();
        $today = today();

        if ($scheduled->lt($today)) {
            return 'vencida';
        }

        if ($scheduled->equalTo($today)) {
            return 'hoy';
        }

        if ($scheduled->lte($today->copy()->addDays(7))) {
            return 'proxima';
        }

        return null;
    }

    protected static function booted(): void
    {
        static::saving(function (FleetWorkOrder $workOrder): void {
            $labor = (float) $workOrder->getAttribute('labor_cost');
            $parts = (float) $workOrder->getAttribute('spare_parts_cost');
            $workOrder->setAttribute('total_cost', number_format($labor + $parts, 2, '.', ''));
        });
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function workProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'work_project_id');
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
     * @return BelongsTo<FleetPreventiveMaintenance, $this>
     */
    public function preventiveMaintenance(): BelongsTo
    {
        return $this->belongsTo(FleetPreventiveMaintenance::class, 'fleet_preventive_maintenance_id');
    }

    /**
     * @return BelongsTo<FleetCorrectiveMaintenance, $this>
     */
    public function correctiveMaintenance(): BelongsTo
    {
        return $this->belongsTo(FleetCorrectiveMaintenance::class, 'fleet_corrective_maintenance_id');
    }

    /**
     * @return BelongsTo<FleetTechnicalInspection, $this>
     */
    public function technicalInspection(): BelongsTo
    {
        return $this->belongsTo(FleetTechnicalInspection::class, 'fleet_technical_inspection_id');
    }

    /**
     * @return HasMany<FleetSparePartMovement, $this>
     */
    public function sparePartMovements(): HasMany
    {
        return $this->hasMany(FleetSparePartMovement::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('service_orders')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
        $this->addMediaCollection('invoices');
        $this->addMediaCollection('quotes');
        $this->addMediaCollection('delivery_acts')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
        $this->addMediaCollection('technical_reports');
    }
}
