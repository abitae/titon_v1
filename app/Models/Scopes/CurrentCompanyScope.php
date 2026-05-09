<?php

namespace App\Models\Scopes;

use App\Services\Companies\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CurrentCompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ((app()->runningInConsole() && ! app()->runningUnitTests()) || ! auth()->check()) {
            return;
        }

        $companyId = session(CompanyContext::SESSION_KEY);

        if ($companyId === null || ! $builder->getModel()->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'company_id')) {
            return;
        }

        $builder->where($model->qualifyColumn('company_id'), $companyId);
    }
}
