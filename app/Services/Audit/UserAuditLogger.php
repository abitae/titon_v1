<?php

namespace App\Services\Audit;

use App\Models\Audit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserAuditLogger
{
    public function __construct(
        protected AuditContextResolver $auditContextResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function log(
        string $action,
        string $module,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        ?string $observation = null,
        ?User $actor = null,
        ?int $companyId = null,
    ): Audit {
        $resolvedActor = $actor ?? auth()->user();
        $resolvedCompanyId = $companyId ?? $this->auditContextResolver->companyIdFor($auditable);
        $resolvedActorType = $resolvedActor !== null ? $resolvedActor::class : null;

        $target = $auditable ?? $resolvedActor;
        $targetType = $target !== null ? $target::class : null;

        return Audit::query()->create([
            'user_type' => $resolvedActorType,
            'user_id' => $resolvedActor?->id,
            'event' => $action,
            'auditable_type' => $targetType,
            'auditable_id' => $target?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => request()?->fullUrl(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'tags' => implode(',', [$module, $action]),
            'company_id' => $resolvedCompanyId,
            'user_name' => $resolvedActor?->name,
            'active_role' => $this->auditContextResolver->activeRoleFor($resolvedActor, $resolvedCompanyId),
            'module' => $module,
            'action' => $action,
            'browser' => $this->auditContextResolver->browserFromAgent((string) request()?->userAgent()),
            'device' => $this->auditContextResolver->deviceFromAgent((string) request()?->userAgent()),
            'observation' => $observation,
        ]);
    }
}
