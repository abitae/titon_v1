<?php

namespace App\Models;

use App\Concerns\BelongsToActiveCompany;
use Database\Factories\DocumentObservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentObservation extends Model
{
    /** @use HasFactory<DocumentObservationFactory> */
    use BelongsToActiveCompany, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'document_id',
        'user_id',
        'observation',
        'status_after',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
