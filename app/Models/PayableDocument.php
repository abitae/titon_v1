<?php

namespace App\Models;

use App\Concerns\BelongsToActiveCompany;
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
        $this->addMediaCollection('archivo');
    }
}
