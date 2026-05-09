<?php

namespace App\Services\Audit;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuditContextResolver
{
    public function __construct(
        protected ResolveCurrentCompany $resolveCurrentCompany,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function metadataFor(Model $model, array $data): array
    {
        $companyId = $this->companyIdFor($model);
        $actor = auth()->user();

        return [
            'company_id' => $companyId,
            'user_name' => $actor?->name,
            'active_role' => $this->activeRoleFor($actor, $companyId),
            'module' => $this->moduleFor($model),
            'action' => $this->actionFor((string) ($data['event'] ?? 'updated'), $data),
            'browser' => $this->browserFromAgent((string) request()->userAgent()),
            'device' => $this->deviceFromAgent((string) request()->userAgent()),
            'observation' => null,
        ];
    }

    public function companyIdFor(?Model $model = null): ?int
    {
        if ($model !== null && isset($model->company_id) && $model->company_id !== null) {
            return (int) $model->company_id;
        }

        return auth()->check()
            ? $this->resolveCurrentCompany->handle(auth()->user())?->id
            : null;
    }

    public function currentCompany(?User $user = null): ?Company
    {
        $resolvedUser = $user ?? auth()->user();

        return $resolvedUser instanceof User
            ? $this->resolveCurrentCompany->handle($resolvedUser)
            : null;
    }

    public function activeRoleFor(?User $user, ?int $companyId): ?string
    {
        if (! $user instanceof User || $companyId === null) {
            return null;
        }

        $roleId = $user->companies()
            ->whereKey($companyId)
            ->value('company_user.role_id');

        return $roleId !== null
            ? Role::query()->whereKey($roleId)->value('name')
            : null;
    }

    public function moduleFor(Model|string|null $subject): string
    {
        $className = $subject instanceof Model ? $subject::class : (string) $subject;

        return match (class_basename($className)) {
            'User' => 'Usuarios',
            'Company' => 'Empresas',
            'Project' => 'Obras',
            'Supplier' => 'Proveedores',
            'Document' => 'Documentos',
            'PurchaseRequest' => 'Solicitudes de compra',
            'SupplierQuotation' => 'Cotizaciones',
            'PurchaseOrder' => 'Ordenes de compra',
            'SupplierContract' => 'Contratos',
            'ContractPaymentSchedule' => 'Cronograma de pagos',
            'SupplierPayment' => 'Pagos a proveedores',
            'FleetEquipment' => 'Mecanica · Equipos',
            'FleetTechnicalInspection' => 'Mecanica · Revisiones tecnicas',
            'FleetPreventiveMaintenance' => 'Mecanica · Mantenimiento preventivo',
            'FleetCorrectiveMaintenance' => 'Mecanica · Mantenimiento correctivo',
            'FleetWorkOrder' => 'Mecanica · Ordenes de trabajo',
            'FleetSparePart' => 'Mecanica · Repuestos',
            'FleetSparePartMovement' => 'Mecanica · Movimientos de repuesto',
            'Audit' => 'Auditoria',
            default => 'Sistema',
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function actionFor(string $event, array $data = []): string
    {
        if ($event === 'created') {
            return 'creacion';
        }

        if ($event === 'deleted') {
            return 'eliminacion';
        }

        if ($event === 'restored') {
            return 'restauracion';
        }

        if ($event !== 'updated') {
            return Str::of($event)->snake()->toString();
        }

        $newStatus = Str::of((string) Arr::get($data, 'new_values.status', ''))->lower()->toString();
        $decision = Str::of((string) Arr::get($data, 'new_values.decision', ''))->lower()->toString();

        return match (true) {
            Str::contains($newStatus, ['aprob']) || Str::contains($decision, 'approved') => 'aprobacion',
            Str::contains($newStatus, ['rechaz']) || Str::contains($decision, 'rejected') => 'rechazo',
            Str::contains($newStatus, ['observ']) => 'observacion',
            Str::contains($newStatus, ['anulad']) => 'anulacion',
            Str::contains($newStatus, ['cerrad']) => 'cierre',
            Str::contains($newStatus, ['derivad']) => 'derivacion',
            Str::contains($newStatus, ['recibid']) => 'recepcion',
            default => 'edicion',
        };
    }

    public function browserFromAgent(string $userAgent): ?string
    {
        return match (true) {
            Str::contains($userAgent, 'Edg') => 'Edge',
            Str::contains($userAgent, 'Chrome') => 'Chrome',
            Str::contains($userAgent, 'Firefox') => 'Firefox',
            Str::contains($userAgent, 'Safari') && ! Str::contains($userAgent, 'Chrome') => 'Safari',
            Str::contains($userAgent, 'Opera') || Str::contains($userAgent, 'OPR/') => 'Opera',
            default => $userAgent !== '' ? 'Desconocido' : null,
        };
    }

    public function deviceFromAgent(string $userAgent): ?string
    {
        return match (true) {
            Str::contains($userAgent, ['Mobile', 'Android', 'iPhone']) => 'Movil',
            Str::contains($userAgent, ['iPad', 'Tablet']) => 'Tablet',
            $userAgent !== '' => 'Escritorio',
            default => null,
        };
    }
}
