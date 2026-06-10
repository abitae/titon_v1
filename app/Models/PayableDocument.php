<?php

namespace App\Models;

use App\Concerns\BelongsToActiveCompany;
use App\Enums\PayableDocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PayableDocument extends Model implements HasMedia
{
    use BelongsToActiveCompany, InteractsWithMedia;

    protected $fillable = [
        'company_id',
        'accounts_payable_id',
        'document_type',
        'required',
        'uploaded',
        'uploaded_by',
        'uploaded_at',
        'status',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'uploaded' => 'boolean',
            'uploaded_at' => 'datetime',
        ];
    }

    public function accountsPayable(): BelongsTo
    {
        return $this->belongsTo(AccountsPayable::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('archivo')->singleFile();
    }

    public function typeLabel(): string
    {
        foreach (PayableDocumentType::cases() as $type) {
            if ($type->value() === $this->document_type) {
                return $type->label();
            }
        }

        return str($this->document_type)->replace('_', ' ')->title()->toString();
    }

    public function hasUploadedFile(): bool
    {
        $media = $this->getFirstMedia('archivo');

        return $media !== null && is_file($media->getPath());
    }
}
