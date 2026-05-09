<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Services\Audit\UserAuditLogger;
use Spatie\Permission\Models\Role;

class SyncUserCompanies
{
    public function __construct(
        protected UserAuditLogger $userAuditLogger,
    ) {}

    /**
     * @param  array<int, int|string>  $companyIds
     * @param  array<int|string, int|string|null>  $roleIds
     * @param  array<int, int|string>  $activeCompanyIds
     */
    public function handle(
        User $user,
        array $companyIds,
        array $roleIds,
        array $activeCompanyIds,
        ?int $defaultCompanyId,
    ): void {
        $previousAssignments = $user->companies()
            ->withPivot(['role_id', 'active', 'default_company'])
            ->get()
            ->map(fn ($company): array => [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'role_id' => $company->pivot->role_id,
                'active' => (bool) $company->pivot->active,
                'default_company' => (bool) $company->pivot->default_company,
            ])
            ->all();

        $selectedCompanyIds = collect($companyIds)
            ->map(fn (int|string $companyId): int => (int) $companyId)
            ->unique()
            ->values();

        $activeIds = collect($activeCompanyIds)
            ->map(fn (int|string $companyId): int => (int) $companyId)
            ->intersect($selectedCompanyIds)
            ->values();

        $roles = Role::query()
            ->whereIn('id', collect($roleIds)->filter()->values())
            ->get()
            ->keyBy('id');

        $currentCompanyIds = $user->companies()->pluck('companies.id')->map(fn ($id): int => (int) $id);
        $removedCompanyIds = $currentCompanyIds->diff($selectedCompanyIds);

        $syncPayload = [];

        foreach ($selectedCompanyIds as $companyId) {
            $syncPayload[$companyId] = [
                'role_id' => (int) $roleIds[$companyId],
                'active' => $activeIds->contains($companyId),
                'default_company' => $defaultCompanyId === $companyId,
            ];
        }

        $previousTeamId = getPermissionsTeamId();

        $user->companies()->sync($syncPayload);

        foreach ($selectedCompanyIds as $companyId) {
            setPermissionsTeamId($companyId);
            $user->unsetRelation('roles')->unsetRelation('permissions');

            $role = $roles->get((int) $roleIds[$companyId]);

            if ($role instanceof Role && $activeIds->contains($companyId)) {
                $user->syncRoles([$role]);
            } else {
                $user->syncRoles([]);
            }
        }

        foreach ($removedCompanyIds as $companyId) {
            setPermissionsTeamId($companyId);
            $user->unsetRelation('roles')->unsetRelation('permissions');
            $user->syncRoles([]);
        }

        setPermissionsTeamId($previousTeamId);
        $user->unsetRelation('roles')->unsetRelation('permissions')->unsetRelation('companies');

        $this->userAuditLogger->log(
            action: 'cambio_roles_permisos',
            module: 'Usuarios',
            auditable: $user,
            oldValues: ['empresas' => $previousAssignments],
            newValues: ['empresas' => $syncPayload],
            observation: 'Actualizacion de empresas activas y roles por empresa.',
        );
    }
}
