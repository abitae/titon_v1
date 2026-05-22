<?php

namespace App\Models;

use App\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationScoreParameter extends Model
{
    use BelongsToActiveCompany;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'max_score',
        'weight',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'max_score' => 'decimal:2',
            'weight' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function scores(): HasMany
    {
        return $this->hasMany(QuotationScore::class);
    }
}
