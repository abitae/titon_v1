<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @deprecated Use Requirement
 */
class PurchaseRequest extends Requirement
{
    protected $table = 'requirements';

    public function items(): HasMany
    {
        return $this->hasMany(RequirementItem::class, 'requirement_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(SupplierQuotation::class, 'requirement_id');
    }

    public function comparison(): HasOne
    {
        return $this->hasOne(QuotationComparison::class, 'requirement_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'requirement_id');
    }
}
