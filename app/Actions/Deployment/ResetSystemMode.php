<?php

namespace App\Actions\Deployment;

use App\Models\ApplicationSetting;
use App\Models\Company;
use App\Models\User;
use App\Services\Application\ApplicationSettingsManager;
use App\Services\Branding\PlatformBranding;
use App\Services\Companies\CompanyContext;
use Database\Seeders\ApplicationSettingSeeder;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\CompanySeeder;
use Database\Seeders\DemoOperationalSeeder;
use Database\Seeders\MechanicsSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SiteContentSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ResetSystemMode
{
    public const Development = 'development';

    public const Production = 'production';

    /**
     * @return array<string, int>
     */
    public function summary(): array
    {
        return [
            'users' => $this->tableCount((new User)->getTable(), $this->superAdminUserIds()),
            'projects' => $this->tableCount('projects'),
            'suppliers' => $this->tableCount('suppliers'),
            'requirements' => $this->tableCount('requirements'),
            'orders' => $this->tableCount('orders'),
            'accounts_payable' => $this->tableCount('accounts_payable'),
            'documents' => $this->tableCount('documents'),
            'warehouse' => $this->tableCount('warehouse_stock_items'),
            'mechanics' => $this->tableCount('fleet_equipments'),
            'bank_accounts' => $this->tableCount('bank_accounts'),
            'contact_messages' => $this->tableCount('contact_messages'),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function handle(string $mode): array
    {
        if (! in_array($mode, [self::Development, self::Production], true)) {
            throw ValidationException::withMessages([
                'mode' => 'El modo solicitado no es valido.',
            ]);
        }

        return Cache::lock('deployment-mode-reset', 120)->block(5, function () use ($mode): array {
            return DB::transaction(function () use ($mode): array {
                $before = $this->summary();

                Schema::withoutForeignKeyConstraints(function () use ($mode): void {
                    $this->purgeOperationalData();
                    $this->purgeNonSuperAdminUsers();

                    if ($mode === self::Development) {
                        $this->seedDevelopmentData();
                    }
                });

                $this->settings()->forceFill(['deployment_mode' => $mode])->save();

                Cache::forget('application-settings.current');
                Cache::forget(ApplicationSettingsManager::CACHE_KEY);
                app(PermissionRegistrar::class)->forgetCachedPermissions();

                $this->refreshAuthenticatedUserContext();

                return [
                    ...$before,
                    'remaining_users' => User::query()->count(),
                ];
            });
        });
    }

    protected function purgeOperationalData(): void
    {
        foreach ($this->purgeTables() as $table) {
            DB::table($table)->delete();
        }
    }

    protected function purgeNonSuperAdminUsers(): void
    {
        $superAdminUserIds = $this->superAdminUserIds();
        $userTable = (new User)->getTable();
        $modelHasRolesTable = config('permission.table_names.model_has_roles');
        $modelHasPermissionsTable = config('permission.table_names.model_has_permissions');

        DB::table($modelHasRolesTable)
            ->where('model_type', User::class)
            ->when(
                $superAdminUserIds !== [],
                fn ($query) => $query->whereNotIn('model_id', $superAdminUserIds),
            )
            ->delete();

        DB::table($modelHasPermissionsTable)
            ->where('model_type', User::class)
            ->when(
                $superAdminUserIds !== [],
                fn ($query) => $query->whereNotIn('model_id', $superAdminUserIds),
            )
            ->delete();

        DB::table('company_user')
            ->when(
                $superAdminUserIds !== [],
                fn ($query) => $query->whereNotIn('user_id', $superAdminUserIds),
            )
            ->delete();

        DB::table($userTable)
            ->when(
                $superAdminUserIds !== [],
                fn ($query) => $query->whereNotIn('id', $superAdminUserIds),
            )
            ->delete();
    }

    protected function seedDevelopmentData(): void
    {
        $guard = Auth::guard();
        $user = $guard->user();

        $guard->forgetUser();

        try {
            foreach ($this->developmentSeeders() as $seeder) {
                app($seeder)->run();
            }
        } finally {
            if ($user !== null) {
                $guard->setUser($user);
            }
        }
    }

    /**
     * @return list<class-string>
     */
    protected function developmentSeeders(): array
    {
        return [
            ApplicationSettingSeeder::class,
            SiteContentSeeder::class,
            PermissionSeeder::class,
            CompanySeeder::class,
            UserSeeder::class,
            CatalogSeeder::class,
            DemoOperationalSeeder::class,
            MechanicsSeeder::class,
        ];
    }

    /**
     * @return list<string>
     */
    protected function purgeTables(): array
    {
        return [
            'media',
            'attachments',
            'document_observations',
            'document_approvals',
            'document_movements',
            'documents',
            'warehouse_movements',
            'warehouse_transfers',
            'warehouse_stock_items',
            'bank_movements',
            'accounts_payable_payments',
            'payable_documents',
            'accounts_payable',
            'supplier_payments',
            'contract_payment_schedules',
            'supplier_contracts',
            'order_conformities',
            'order_items',
            'orders',
            'quotation_scores',
            'quotation_comparisons',
            'supplier_quotation_items',
            'supplier_quotations',
            'requirement_supplier_invitations',
            'quotation_score_parameters',
            'requirement_items',
            'requirements',
            'fleet_spare_part_movements',
            'fleet_work_orders',
            'fleet_corrective_maintenances',
            'fleet_preventive_maintenances',
            'fleet_technical_inspections',
            'fleet_spare_parts',
            'fleet_equipments',
            'bank_accounts',
            'suppliers',
            'projects',
            'audits',
            'contact_messages',
            'company_correlative_sequences',
        ];
    }

    /**
     * @return list<int>
     */
    protected function superAdminUserIds(): array
    {
        $role = Role::query()
            ->where('name', 'Super Admin')
            ->where('guard_name', 'web')
            ->first();

        if ($role === null) {
            return $this->authenticatedSuperAdminIds();
        }

        $idsFromRoles = DB::table(config('permission.table_names.model_has_roles'))
            ->where('role_id', $role->id)
            ->where('model_type', User::class)
            ->pluck('model_id');

        $idsFromCompanies = DB::table('company_user')
            ->where('role_id', $role->id)
            ->pluck('user_id');

        return collect($idsFromRoles)
            ->merge($idsFromCompanies)
            ->merge($this->authenticatedSuperAdminIds())
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    protected function authenticatedSuperAdminIds(): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [];
        }

        $superAdminRole = Role::query()
            ->where('name', 'Super Admin')
            ->where('guard_name', 'web')
            ->first();

        if ($superAdminRole !== null) {
            $hasPivotSuperAdmin = $user->companies()
                ->wherePivot('role_id', $superAdminRole->id)
                ->exists();

            if ($hasPivotSuperAdmin) {
                return [(int) $user->getKey()];
            }
        }

        $registrar = app(PermissionRegistrar::class);
        $previousTeamId = getPermissionsTeamId();
        $companyIds = $user->companies()->pluck('companies.id');

        foreach ($companyIds as $companyId) {
            setPermissionsTeamId($companyId);
            $registrar->setPermissionsTeamId($companyId);
            $user->unsetRelation('roles')->unsetRelation('permissions');

            if ($user->hasRole('Super Admin')) {
                setPermissionsTeamId($previousTeamId);
                $registrar->setPermissionsTeamId($previousTeamId);
                $user->unsetRelation('roles')->unsetRelation('permissions');

                return [(int) $user->getKey()];
            }
        }

        setPermissionsTeamId($previousTeamId);
        $registrar->setPermissionsTeamId($previousTeamId);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        if ($user->hasRole('Super Admin')) {
            return [(int) $user->getKey()];
        }

        return [];
    }

    protected function refreshAuthenticatedUserContext(): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $user->unsetRelation('roles')->unsetRelation('permissions')->unsetRelation('companies');

        $freshUser = $user->fresh();

        if (! $freshUser instanceof User) {
            return;
        }

        Auth::setUser($freshUser);

        $company = app(CompanyContext::class)->resolveFor($freshUser);

        if ($company instanceof Company) {
            app(CompanyContext::class)->remember($company);
        }
    }

    /**
     * @param  list<int>  $exceptIds
     */
    protected function tableCount(string $table, array $exceptIds = []): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return (int) DB::table($table)
            ->when($exceptIds !== [], fn ($query) => $query->whereNotIn('id', $exceptIds))
            ->count();
    }

    protected function settings(): ApplicationSetting
    {
        return ApplicationSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'application_name' => config('app.name', PlatformBranding::DEFAULT_NAME),
                'deployment_mode' => self::Development,
            ],
        );
    }
}
