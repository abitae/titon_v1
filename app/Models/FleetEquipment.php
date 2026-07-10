<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Database\Factories\FleetEquipmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FleetEquipment extends Model implements Auditable, HasMedia
{
    /** @use HasFactory<FleetEquipmentFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory, InteractsWithMedia;

    protected $table = 'fleet_equipments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'work_project_id',
        'responsible_user_id',
        'internal_code',
        'equipment_type_id',
        'equipment_type',
        'name',
        'brand',
        'model',
        'serial_number',
        'plate',
        'year',
        'color',
        'city',
        'operational_status',
        'odometer_km',
        'hour_meter',
        'acquisition_date',
        'observations',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'odometer_km' => 'decimal:2',
            'hour_meter' => 'decimal:2',
            'acquisition_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<CatalogItem, $this>
     */
    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class, 'equipment_type_id');
    }

    public function typeLabel(): string
    {
        return $this->equipmentType?->name ?? $this->equipment_type ?? '—';
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function workProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'work_project_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * @return HasMany<FleetTechnicalInspection, $this>
     */
    public function technicalInspections(): HasMany
    {
        return $this->hasMany(FleetTechnicalInspection::class);
    }

    /**
     * @return HasMany<FleetPreventiveMaintenance, $this>
     */
    public function preventiveMaintenances(): HasMany
    {
        return $this->hasMany(FleetPreventiveMaintenance::class);
    }

    /**
     * @return HasMany<FleetCorrectiveMaintenance, $this>
     */
    public function correctiveMaintenances(): HasMany
    {
        return $this->hasMany(FleetCorrectiveMaintenance::class);
    }

    /**
     * @return HasMany<FleetWorkOrder, $this>
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(FleetWorkOrder::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('equipment_photos');
        $this->addMediaCollection('equipment_documents')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
    }
}
