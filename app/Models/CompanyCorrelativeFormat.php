<?php

namespace App\Models;

use App\Enums\CorrelativeSubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyCorrelativeFormat extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'subject',
        'series',
        'suffix',
        'template',
        'pad_length',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'pad_length' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function subjectEnum(): CorrelativeSubject
    {
        return CorrelativeSubject::from($this->subject);
    }
}
