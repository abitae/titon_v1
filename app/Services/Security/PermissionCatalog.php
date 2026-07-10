<?php

namespace App\Services\Security;

class PermissionCatalog
{
    /**
     * @var array<string, string>
     */
    protected const MODULE_LABELS = [
        'dashboard' => 'Dashboard',
        'companies' => 'Empresas',
        'users' => 'Usuarios',
        'roles' => 'Roles',
        'permissions' => 'Permisos',
        'catalogs' => 'Catalogos',
        'documents' => 'Documentos',
        'purchases' => 'Compras',
        'projects' => 'Obras',
        'suppliers' => 'Proveedores',
        'contracts' => 'Contratos',
        'payments' => 'Pagos',
        'bancos' => 'Bancos',
        'almacen' => 'Almacen',
        'audits' => 'Auditoria',
        'mecanica' => 'Mecanica',
        'equipos' => 'Equipos',
        'mantenimientos' => 'Mantenimientos',
        'revisiones' => 'Revisiones tecnicas',
        'requerimientos' => 'Requerimientos',
        'cotizaciones' => 'Cotizaciones',
        'ordenes' => 'Ordenes de compra',
        'cuentas_pagar' => 'Cuentas por pagar',
        'pdf-formats' => 'Formatos PDF',
    ];

    /**
     * @var array<string, string>
     */
    protected const ACTION_LABELS = [
        'ver' => 'Ver',
        'crear' => 'Crear',
        'editar' => 'Editar',
        'eliminar' => 'Eliminar',
        'aprobar' => 'Aprobar',
        'exportar' => 'Exportar',
        'cancelar' => 'Cancelar',
        'enviar_proveedor' => 'Enviar a proveedor',
        'evaluar' => 'Evaluar',
        'seleccionar' => 'Seleccionar',
        'emitir' => 'Emitir',
        'anular' => 'Anular',
        'conformidad' => 'Registrar conformidad',
        'rechazar' => 'Rechazar',
        'subir_documentos' => 'Subir documentos',
        'pagar' => 'Registrar pago',
        'mover' => 'Mover stock',
        'transferir' => 'Transferir stock',
        'cerrar' => 'Cerrar',
    ];

    public function describe(string $permissionName): string
    {
        [$module, $action] = array_pad(explode('.', $permissionName, 2), 2, null);

        if ($module === null || $action === null) {
            return $permissionName;
        }

        $moduleLabel = self::MODULE_LABELS[$module] ?? str($module)->replace(['-', '_'], ' ')->title()->toString();
        $actionLabel = self::ACTION_LABELS[$action] ?? str($action)->replace('_', ' ')->title()->toString();

        return "{$actionLabel} en {$moduleLabel}";
    }

    /**
     * @return array<string, string>
     */
    public function moduleLabels(): array
    {
        return self::MODULE_LABELS;
    }

    public function moduleLabel(string $module): string
    {
        return self::MODULE_LABELS[$module] ?? str($module)->replace(['-', '_'], ' ')->title()->toString();
    }

    public function moduleFromPermission(string $permissionName): string
    {
        return explode('.', $permissionName, 2)[0];
    }
}
