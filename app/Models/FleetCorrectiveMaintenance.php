<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Database\Factories\FleetCorrectiveMaintenanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FleetCorrectiveMaintenance extends Model implements Auditable, HasMedia
{
    /** @use HasFactory<FleetCorrectiveMaintenanceFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory, InteractsWithMedia;

    protected $table = 'fleet_corrective_maintenances';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'fleet_equipment_id',
        'code',
        'responsible_user_id',
        'failure_at',
        'failure_description',
        'diagnosis',
        'supplier_workshop',
        'estimated_cost',
        'real_cost',
        'status',
        'observations',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'failure_at' => 'datetime',
            'estimated_cost' => 'decimal:2',
            'real_cost' => 'decimal:2',
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
        return $this->hasMany(FleetWorkOrder::class, 'fleet_corrective_maintenance_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('failure_photos');
        $this->addMediaCollection('corrective_documents')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
    }
}
