<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use App\Enums\DocumentStatus;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends Model implements Auditable, HasMedia
{
    /** @use HasFactory<DocumentFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory, InteractsWithMedia;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'work_project_id',
        'created_by_user_id',
        'code',
        'document_number',
        'document_type_id',
        'subject',
        'description',
        'origin_area_id',
        'destination_area_id',
        'current_user_id',
        'status',
        'priority',
        'issue_date',
        'reception_date',
        'due_date',
        'observations',
        'attended_at',
        'archived_at',
        'cancelled_at',
        'annulment_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'reception_date' => 'date',
            'due_date' => 'date',
            'attended_at' => 'datetime',
            'archived_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function currentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'work_project_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class, 'document_type_id');
    }

    public function originArea(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class, 'origin_area_id');
    }

    public function destinationArea(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class, 'destination_area_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(DocumentMovement::class)->latest();
    }

    public function movementObservations(): HasMany
    {
        return $this->hasMany(DocumentObservation::class)->latest();
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(DocumentApproval::class)->latest();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }

    public function pendingHours(): int
    {
        $reference = $this->reception_date?->startOfDay() ?? $this->created_at;

        if ($reference === null) {
            return 0;
        }

        return $reference->diffInHours(now());
    }

    public function isExpiredForAttention(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && ! in_array($this->status, [
                DocumentStatus::Attended->value(),
                DocumentStatus::Approved->value(),
                DocumentStatus::Rejected->value(),
                DocumentStatus::Archived->value(),
                DocumentStatus::Cancelled->value(),
                DocumentStatus::Closed->value(),
                DocumentStatus::Expired->value(),
            ], true);
    }

    public function currentLocationLabel(): string
    {
        return $this->destinationArea?->name
            ?? $this->originArea?->name
            ?? 'Sin area';
    }
}
