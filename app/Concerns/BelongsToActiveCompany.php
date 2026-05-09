<?php

namespace App\Concerns;

use App\Models\Company;
use App\Models\Scopes\CurrentCompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToActiveCompany
{
    public static function bootBelongsToActiveCompany(): void
    {
        static::addGlobalScope(new CurrentCompanyScope);
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
