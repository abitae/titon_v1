<?php

namespace App\Services\Navigation;

use App\Enums\PlatformModule;
use Illuminate\Support\Facades\Auth;

class AppNavigation
{
    /**
     * @return array<int, array<string, string|bool>>
     */
    public function sidebarPrimaryItems(): array
    {
        return $this->filterVisibleItems([
            $this->item(PlatformModule::Dashboard),
        ]);
    }

    /**
     * @return array<int, array{heading: string, items: array<int, array<string, string|bool>>}>
     */
    public function sidebarGroups(): array
    {
        $groups = [
            [
                'heading' => 'Compras',
                'items' => $this->procurementItems(),
            ],
            [
                'heading' => 'Operacion',
                'items' => array_map(
                    fn (PlatformModule $module): array => $this->item($module),
                    PlatformModule::businessModules(),
                ),
            ],
            [
                'heading' => 'Mecanica',
                'items' => $this->mechanicsItems(),
            ],
            [
                'heading' => 'Seguridad',
                'items' => [
                    $this->navItem(
                        label: 'Empresas',
                        description: 'Administracion de empresas y branding.',
                        icon: 'building-office',
                        route: 'companies.index',
                        current: request()->routeIs('companies.*'),
                        permission: 'companies.ver',
                    ),
                    $this->navItem(
                        label: 'Usuarios',
                        description: 'Accesos, empresas y roles por usuario.',
                        icon: 'user-group',
                        route: 'users.index',
                        current: request()->routeIs('users.*'),
                        permission: 'users.ver',
                    ),
                    $this->navItem(
                        label: 'Roles',
                        description: 'Perfiles de acceso y permisos asignados.',
                        icon: 'identification',
                        route: 'security.roles',
                        current: request()->routeIs('security.roles'),
                        permission: 'roles.ver',
                    ),
                    $this->navItem(
                        label: 'Permisos',
                        description: 'Catalogo de permisos del sistema.',
                        icon: 'key',
                        route: 'security.permissions',
                        current: request()->routeIs('security.permissions'),
                        permission: 'permissions.ver',
                    ),
                    $this->navItem(
                        label: 'Auditoria',
                        description: 'Trazabilidad de acciones por usuario y empresa.',
                        icon: 'shield-check',
                        route: 'audits.users',
                        current: request()->routeIs('audits.*'),
                        permission: 'audits.ver',
                    ),
                ],
            ],
            [
                'heading' => 'Configuracion',
                'items' => [
                    $this->navItem(
                        label: 'General',
                        description: 'Catalogos base para operación.',
                        icon: 'cog-6-tooth',
                        route: 'settings.catalogs',
                        current: request()->routeIs('settings.catalogs'),
                        permission: 'catalogs.ver',
                    ),
                    $this->navItem(
                        label: 'Correlativos',
                        description: 'Formato de codigos automaticos por modulo y año.',
                        icon: 'hashtag',
                        route: 'settings.correlatives',
                        current: request()->routeIs('settings.correlatives'),
                        permission: 'catalogs.ver',
                    ),
                    $this->navItem(
                        label: 'Formatos PDF',
                        description: 'Membrete, logo y colores de exportaciones PDF.',
                        icon: 'document-text',
                        route: 'settings.pdf-formats',
                        current: request()->routeIs('settings.pdf-formats*'),
                        permission: 'pdf-formats.ver',
                    ),
                    $this->navItem(
                        label: 'Tipos de costo',
                        description: 'Clasificacion de costos para requerimientos.',
                        icon: 'banknotes',
                        route: 'settings.cost-types',
                        current: request()->routeIs('settings.cost-types'),
                        permission: 'catalogs.ver',
                    ),
                    $this->navItem(
                        label: 'Produccion',
                        description: 'Limpieza de datos demo y modo del sistema.',
                        icon: 'rocket-launch',
                        route: 'settings.deployment-mode',
                        current: request()->routeIs('settings.deployment-mode'),
                        permission: 'deployment.ver',
                    ),
                ],
            ],
        ];

        if (Auth::user()?->hasRole('Super Admin')) {
            $groups[] = [
                'heading' => 'Sitio web',
                'items' => [
                    $this->navItem(
                        label: 'Contenido',
                        description: 'Textos e imágenes del sitio público.',
                        icon: 'document-text',
                        route: 'admin.site-content',
                        current: request()->routeIs('admin.site-content'),
                    ),
                    $this->navItem(
                        label: 'Portafolio',
                        description: 'Proyectos publicados en el sitio.',
                        icon: 'photo',
                        route: 'admin.showcase-projects',
                        current: request()->routeIs('admin.showcase-projects'),
                    ),
                    $this->navItem(
                        label: 'Mensajes',
                        description: 'Formulario de contacto del sitio.',
                        icon: 'envelope',
                        route: 'admin.contact-messages',
                        current: request()->routeIs('admin.contact-messages'),
                    ),
                ],
            ];
        }

        return $this->filterVisibleGroups($groups);
    }

    /**
     * @param  array<int, array<string, string|bool>>  $items
     * @return array<int, array<string, string|bool>>
     */
    protected function filterVisibleItems(array $items): array
    {
        return collect($items)
            ->filter(fn (array $item): bool => $this->canSeeNavItem($item))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{heading: string, items: array<int, array<string, string|bool>>}>  $groups
     * @return array<int, array{heading: string, items: array<int, array<string, string|bool>>}>
     */
    protected function filterVisibleGroups(array $groups): array
    {
        return collect($groups)
            ->map(function (array $group): array {
                $group['items'] = collect($group['items'])
                    ->filter(fn (array $item): bool => $this->canSeeNavItem($item))
                    ->values()
                    ->all();

                return $group;
            })
            ->filter(fn (array $group): bool => $group['items'] !== [])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string|bool>  $item
     */
    protected function canSeeNavItem(array $item): bool
    {
        $permission = $item['permission'] ?? null;

        if (! is_string($permission) || $permission === '') {
            return true;
        }

        return Auth::user()?->can($permission) ?? false;
    }

    /**
     * @return array<int, array<string, string|bool>>
     */
    protected function procurementItems(): array
    {
        return [
            $this->navItem(
                label: 'Requerimientos',
                description: 'Solicitudes, cotizaciones y comparacion por obra.',
                icon: 'clipboard-document-list',
                route: 'modules.purchases',
                current: request()->routeIs([
                    'modules.purchases',
                    'purchases.send-suppliers',
                    'purchases.quotations',
                    'purchases.comparison',
                    'purchases.winner',
                    'purchases.comparison.pdf',
                    'purchases.order.pdf',
                ]),
                permission: 'purchases.ver',
            ),
            $this->navItem(
                label: 'Ordenes de compra',
                description: 'Emision, conformidad y anulacion de OC.',
                icon: 'shopping-cart',
                route: 'purchases.orders',
                current: request()->routeIs([
                    'purchases.orders',
                    'purchases.orders.pdf',
                    'purchases.orders.pdf.preview',
                ]),
                permission: 'purchases.ver',
            ),
            $this->navItem(
                label: 'Cuentas por pagar',
                description: 'Pago de ordenes conformes y documentos CxP.',
                icon: 'credit-card',
                route: 'accounts-payable.index',
                current: request()->routeIs('accounts-payable.*'),
                permission: 'payments.ver',
            ),
        ];
    }

    /**
     * @return array<int, array<string, string|bool>>
     */
    protected function mechanicsItems(): array
    {
        return [
            $this->navItem(
                label: 'Panel',
                description: 'Graficos e indicadores visuales de mecanica.',
                icon: 'chart-bar',
                route: 'modules.mechanics',
                current: request()->routeIs('modules.mechanics'),
                permission: 'mecanica.ver',
            ),
            $this->navItem(
                label: 'Reportes',
                description: 'Exportaciones PDF y Excel del modulo.',
                icon: 'document-chart-bar',
                route: 'mechanics.reports',
                current: request()->routeIs('mechanics.reports') || request()->routeIs('mechanics.report.*'),
                permission: 'mecanica.ver',
            ),
            $this->navItem(
                label: 'Equipos',
                description: 'Registro y estado de maquinaria por empresa.',
                icon: 'truck',
                route: 'mechanics.equipments',
                current: request()->routeIs([
                    'mechanics.equipments',
                    'mechanics.equipment-types',
                ]),
                permission: 'equipos.ver',
            ),
            $this->navItem(
                label: 'Tipos de equipo',
                description: 'Catalogo de clasificacion de maquinaria.',
                icon: 'tag',
                route: 'mechanics.equipment-types',
                current: request()->routeIs('mechanics.equipment-types'),
                permission: 'equipos.ver',
            ),
            $this->navItem(
                label: 'Revisiones tecnicas',
                description: 'Inspecciones programadas y vencimientos.',
                icon: 'clipboard-document-check',
                route: 'mechanics.inspections',
                current: request()->routeIs('mechanics.inspections'),
                permission: 'revisiones.ver',
            ),
            $this->navItem(
                label: 'Mantenimiento preventivo',
                description: 'Planes y ejecucion de mantenimiento programado.',
                icon: 'calendar-days',
                route: 'mechanics.preventive',
                current: request()->routeIs('mechanics.preventive'),
                permission: 'mantenimientos.ver',
            ),
            $this->navItem(
                label: 'Mantenimiento correctivo',
                description: 'Intervenciones por falla o averia.',
                icon: 'wrench',
                route: 'mechanics.corrective',
                current: request()->routeIs('mechanics.corrective'),
                permission: 'mantenimientos.ver',
            ),
            $this->navItem(
                label: 'Ordenes de trabajo',
                description: 'OT abiertas, tecnicos, costos y seguimiento.',
                icon: 'clipboard-document-list',
                route: 'mechanics.work-orders',
                current: request()->routeIs('mechanics.work-orders'),
                permission: 'mantenimientos.ver',
            ),
            $this->navItem(
                label: 'Repuestos',
                description: 'Stock, movimientos y consumo de repuestos.',
                icon: 'cube',
                route: 'mechanics.spare-parts',
                current: request()->routeIs('mechanics.spare-parts'),
                permission: 'mecanica.ver',
            ),
        ];
    }

    /**
     * @return array<string, string|bool>
     */
    protected function item(PlatformModule $module): array
    {
        return $this->navItem(
            label: $module->label(),
            description: $module->description(),
            icon: $module->icon(),
            route: $module->routeName(),
            current: $module->isNavCurrent(),
            permission: $module->viewPermission(),
        );
    }

    /**
     * @return array<string, string|bool>
     */
    protected function navItem(
        string $label,
        string $description,
        string $icon,
        string $route,
        bool|array $current,
        ?string $permission = null,
    ): array {
        return [
            'label' => $label,
            'description' => $description,
            'icon' => $icon,
            'route' => $route,
            'href' => route($route),
            'current' => $current,
            'permission' => $permission ?? '',
        ];
    }
}
