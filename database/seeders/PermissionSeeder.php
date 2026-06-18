<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
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

        $modules = [
            'dashboard',
            'companies',
            'users',
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
                Permission::findOrCreate($module.'.'.$action, 'web');
            }
        }

        $mechanicalGranular = [
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
        ];

        foreach ($mechanicalGranular as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $procurementGranular = [
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
        ];

        foreach ($procurementGranular as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $warehouseGranular = [
            'almacen.ver',
            'almacen.mover',
            'almacen.transferir',
            'almacen.exportar',
        ];

        foreach ($warehouseGranular as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $roles = [
            'Super Admin' => Permission::query()->pluck('name')->all(),
            'Gerencia' => [
                'dashboard.ver',
                'companies.ver',
                'users.ver',
                'documents.ver',
                'documents.aprobar',
                'documents.exportar',
                'projects.ver',
                'projects.aprobar',
                'projects.exportar',
                'suppliers.ver',
                'contracts.ver',
                'contracts.aprobar',
                'payments.ver',
                'payments.aprobar',
                'payments.exportar',
                'audits.ver',
                'audits.exportar',
                'mecanica.ver',
                'mecanica.exportar',
                'mecanica.aprobar',
                'equipos.ver',
                'revisiones.ver',
                'revisiones.exportar',
                'mantenimientos.ver',
                'almacen.ver',
                'almacen.mover',
                'almacen.transferir',
                'almacen.exportar',
            ],
            'Administrador' => [
                'dashboard.ver',
                'companies.ver',
                'companies.crear',
                'companies.editar',
                'users.ver',
                'users.crear',
                'users.editar',
                'users.eliminar',
                'catalogs.ver',
                'catalogs.crear',
                'catalogs.editar',
                'catalogs.eliminar',
                'documents.ver',
                'documents.crear',
                'documents.editar',
                'documents.eliminar',
                'documents.exportar',
                'purchases.ver',
                'purchases.crear',
                'purchases.editar',
                'purchases.eliminar',
                'purchases.aprobar',
                'purchases.exportar',
                'projects.ver',
                'projects.crear',
                'projects.editar',
                'projects.eliminar',
                'suppliers.ver',
                'suppliers.crear',
                'suppliers.editar',
                'suppliers.eliminar',
                'contracts.ver',
                'contracts.crear',
                'contracts.editar',
                'contracts.eliminar',
                'contracts.aprobar',
                'contracts.exportar',
                'payments.ver',
                'payments.crear',
                'payments.editar',
                'payments.eliminar',
                'payments.exportar',
                'audits.ver',
                'audits.exportar',
                'mecanica.ver',
                'mecanica.crear',
                'mecanica.editar',
                'mecanica.eliminar',
                'mecanica.aprobar',
                'mecanica.exportar',
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
                'almacen.ver',
                'almacen.mover',
                'almacen.transferir',
                'almacen.exportar',
            ],
            'Compras' => [
                'dashboard.ver',
                'catalogs.ver',
                'documents.ver',
                'documents.crear',
                'documents.editar',
                'purchases.ver',
                'purchases.crear',
                'purchases.editar',
                'purchases.aprobar',
                'purchases.exportar',
                'projects.ver',
                'suppliers.ver',
                'suppliers.crear',
                'suppliers.editar',
                'contracts.ver',
                'contracts.crear',
                'contracts.editar',
                'contracts.aprobar',
                'contracts.exportar',
                'audits.ver',
                'mecanica.ver',
                'equipos.ver',
                'mantenimientos.ver',
                'revisiones.ver',
                'almacen.ver',
            ],
            'Finanzas' => [
                'dashboard.ver',
                'catalogs.ver',
                'documents.ver',
                'purchases.ver',
                'contracts.ver',
                'contracts.aprobar',
                'payments.ver',
                'payments.crear',
                'payments.editar',
                'payments.aprobar',
                'payments.exportar',
                'audits.ver',
                'mecanica.ver',
                'equipos.ver',
                'mantenimientos.ver',
                'revisiones.ver',
            ],
            'Responsable de Obra' => [
                'dashboard.ver',
                'catalogs.ver',
                'documents.ver',
                'documents.crear',
                'purchases.ver',
                'projects.ver',
                'projects.crear',
                'projects.editar',
                'suppliers.ver',
                'contracts.ver',
                'audits.ver',
                'mecanica.ver',
                'equipos.ver',
                'equipos.crear',
                'equipos.editar',
                'mantenimientos.ver',
                'mantenimientos.crear',
                'mantenimientos.cerrar',
                'revisiones.ver',
                'revisiones.crear',
                'almacen.ver',
                'almacen.mover',
                'almacen.transferir',
            ],
            'Consulta' => [
                'dashboard.ver',
                'companies.ver',
                'users.ver',
                'catalogs.ver',
                'documents.ver',
                'purchases.ver',
                'projects.ver',
                'suppliers.ver',
                'contracts.ver',
                'payments.ver',
                'audits.ver',
                'mecanica.ver',
                'equipos.ver',
                'mantenimientos.ver',
                'revisiones.ver',
                'almacen.ver',
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }
    }
}
