<?php

namespace App\Models;

use App\Concerns\BelongsToActiveCompany;
use App\Enums\CatalogType;
use Database\Factories\CatalogItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogItem extends Model
{
    /** @use HasFactory<CatalogItemFactory> */
    use BelongsToActiveCompany, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'type',
        'name',
        'code',
        'description',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'bool',
        ];
    }

    public function scopeOfType(Builder $query, CatalogType|string $type): Builder
    {
        $typeValue = $type instanceof CatalogType ? $type->value() : $type;

        return $query->where('type', $typeValue);
    }

    public function requiresBankingDetails(): bool
    {
        return strtoupper($this->code) !== 'EFE';
    }
}
