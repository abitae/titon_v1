<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Services\Security\PermissionCatalog;
use App\Services\Security\RolePermissionMatrix;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $catalog = app(PermissionCatalog::class);

        $modules = [
            'dashboard',
            'companies',
            'users',
            'roles',
            'permissions',
            'catalogs',
            'documents',
            'purchases',
            'projects',
            'suppliers',
            'contracts',
            'payments',
            'bancos',
            'almacen',
            'audits',
            'mecanica',
        ];

        $actions = ['ver', 'crear', 'editar', 'eliminar', 'aprobar', 'exportar'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $this->upsertPermission($module.'.'.$action, $catalog);
            }
        }

        $granular = [
            'equipos.ver',
            'equipos.crear',
            'equipos.editar',
            'equipos.eliminar',
            'mantenimientos.ver',
            'mantenimientos.crear',
            'mantenimientos.cerrar',
            'revisiones.ver',
            'revisiones.crear',
            'revisiones.exportar',
            'requerimientos.ver',
            'requerimientos.crear',
            'requerimientos.editar',
            'requerimientos.cancelar',
            'requerimientos.enviar_proveedor',
            'cotizaciones.ver',
            'cotizaciones.crear',
            'cotizaciones.evaluar',
            'cotizaciones.seleccionar',
            'ordenes.ver',
            'ordenes.crear',
            'ordenes.emitir',
            'ordenes.anular',
            'ordenes.conformidad',
            'ordenes.rechazar',
            'cuentas_pagar.ver',
            'cuentas_pagar.subir_documentos',
            'cuentas_pagar.pagar',
            'cuentas_pagar.exportar',
            'almacen.ver',
            'almacen.mover',
            'almacen.transferir',
            'almacen.exportar',
            'pdf-formats.ver',
            'pdf-formats.editar',
            'deployment.ver',
            'deployment.editar',
        ];

        foreach ($granular as $permissionName) {
            $this->upsertPermission($permissionName, $catalog);
        }

        $roles = RolePermissionMatrix::definitions();
        $roles['Super Admin'] = Permission::query()->pluck('name')->all();

        foreach ($roles as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }
    }

    protected function upsertPermission(string $name, PermissionCatalog $catalog): void
    {
        Permission::query()->updateOrCreate(
            ['name' => $name, 'guard_name' => 'web'],
            ['description' => $catalog->describe($name)],
        );
    }
}
