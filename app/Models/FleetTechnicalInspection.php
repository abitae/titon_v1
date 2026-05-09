<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use App\Enums\FleetTechnicalInspectionStatus;
use Database\Factories\FleetTechnicalInspectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FleetTechnicalInspection extends Model implements Auditable, HasMedia
{
    /** @use HasFactory<FleetTechnicalInspectionFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory, InteractsWithMedia;

    protected $table = 'fleet_technical_inspections';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'fleet_equipment_id',
        'code',
        'responsible_user_id',
        'reviewed_at',
        'due_at',
        'result',
        'inspection_center',
        'observations',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'date',
            'due_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (FleetTechnicalInspection $inspection): void {
            $inspection->applyDerivedStatus();
        });
    }

    public function applyDerivedStatus(): void
    {
        $resultLower = mb_strtolower($this->result);

        if (str_contains($resultLower, 'observ')) {
            $this->status = FleetTechnicalInspectionStatus::Observed->value();

            return;
        }

        $today = now()->startOfDay();
        $due = $this->due_at?->copy()->startOfDay();

        if ($due !== null && $due->lt($today)) {
            $this->status = FleetTechnicalInspectionStatus::Expired->value();

            return;
        }

        if ($due !== null && $due->lte($today->copy()->addDays(30))) {
            $this->status = FleetTechnicalInspectionStatus::DueSoon->value();

            return;
        }

        $this->status = FleetTechnicalInspectionStatus::Valid->value();
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
     * @return HasOne<FleetWorkOrder, $this>
     */
    public function workOrder(): HasOne
    {
        return $this->hasOne(FleetWorkOrder::class, 'fleet_technical_inspection_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('certificate')
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
    }
}
