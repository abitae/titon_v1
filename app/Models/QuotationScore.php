<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationScore extends Model
{
    protected $fillable = [
        'supplier_quotation_id',
        'quotation_score_parameter_id',
        'score',
        'weighted_score',
        'evaluated_by',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'weighted_score' => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(SupplierQuotation::class, 'supplier_quotation_id');
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(QuotationScoreParameter::class, 'quotation_score_parameter_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
