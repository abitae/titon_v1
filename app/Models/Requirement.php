<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Database\Factories\RequirementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Requirement extends Model implements Auditable, HasMedia
{
    /** @use HasFactory<RequirementFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory, InteractsWithMedia, SoftDeletes;

    protected $table = 'requirements';

    protected $fillable = [
        'company_id',
        'work_project_id',
        'responsible_user_id',
        'requested_by',
        'code',
        'title',
        'description',
        'requirement_type',
        'cost_type_id',
        'priority',
        'requested_by_name',
        'request_date',
        'needed_date',
        'status',
        'observation',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'needed_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'work_project_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function costType(): BelongsTo
    {
        return $this->belongsTo(CostType::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequirementItem::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(RequirementSupplierInvitation::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(SupplierQuotation::class);
    }

    public function comparison(): HasOne
    {
        return $this->hasOne(QuotationComparison::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('sustentos');
    }
}
