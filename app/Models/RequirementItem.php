<?php

namespace App\Models;

use App\Concerns\BelongsToActiveCompany;
use Database\Factories\RequirementItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequirementItem extends Model
{
    /** @use HasFactory<RequirementItemFactory> */
    use BelongsToActiveCompany, HasFactory;

    protected $table = 'requirement_items';

    protected $fillable = [
        'company_id',
        'work_project_id',
        'requirement_id',
        'item_type',
        'cost_center_ua',
        'description',
        'unit',
        'quantity',
        'technical_specification',
        'estimated_unit_price',
        'estimated_total',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'estimated_unit_price' => 'decimal:2',
            'estimated_total' => 'decimal:2',
        ];
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }
}
