<?php

namespace App\Models\Scopes;

use App\Services\Companies\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CurrentCompanyScope implements Scope
{
    /** @var array<string, bool> */
    private static array $companyColumnCache = [];

    public function apply(Builder $builder, Model $model): void
    {
        if ((app()->runningInConsole() && ! app()->runningUnitTests()) || ! auth()->check()) {
            return;
        }

        $table = $model->getTable();

        if (! array_key_exists($table, self::$companyColumnCache)) {
            self::$companyColumnCache[$table] = $model->getConnection()
                ->getSchemaBuilder()
                ->hasColumn($table, 'company_id');
        }

        if (! self::$companyColumnCache[$table]) {
            return;
        }

        $companyId = session(CompanyContext::SESSION_KEY);

        if ($companyId === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where($model->qualifyColumn('company_id'), $companyId);
    }
}
