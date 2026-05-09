<?php

namespace App\Services\Navigation;

use App\Enums\PlatformModule;

class AppNavigation
{
    /**
     * @return array<int, array{heading: string, items: array<int, array<string, string|bool>>}>
     */
    public function sidebarGroups(): array
    {
        return [
            [
                'heading' => 'Plataforma',
                'items' => [
                    $this->item(PlatformModule::Dashboard),
                ],
            ],
            [
                'heading' => 'Seguridad',
                'items' => [
                    [
                        'label' => 'Empresas',
                        'description' => 'Administracion de empresas y branding.',
                        'icon' => 'building-office',
                        'route' => 'companies.index',
                        'href' => route('companies.index'),
                        'current' => request()->routeIs('companies.*'),
                    ],
                    [
                        'label' => 'Usuarios',
                        'description' => 'Accesos, empresas y roles por usuario.',
                        'icon' => 'user-group',
                        'route' => 'users.index',
                        'href' => route('users.index'),
                        'current' => request()->routeIs('users.*'),
                    ],
                    [
                        'label' => 'Auditoria',
                        'description' => 'Trazabilidad de acciones por usuario y empresa.',
                        'icon' => 'shield-check',
                        'route' => 'audits.users',
                        'href' => route('audits.users'),
                        'current' => request()->routeIs('audits.*'),
                    ],
                ],
            ],
            [
                'heading' => 'Configuracion',
                'items' => [
                    [
                        'label' => 'General',
                        'description' => 'Catalogos base para operación.',
                        'icon' => 'cog-6-tooth',
                        'route' => 'settings.catalogs',
                        'href' => route('settings.catalogs'),
                        'current' => request()->routeIs('settings.catalogs'),
                    ],
                    [
                        'label' => 'Correlativos',
                        'description' => 'Formato de codigos automaticos por modulo y año.',
                        'icon' => 'hashtag',
                        'route' => 'settings.correlatives',
                        'href' => route('settings.correlatives'),
                        'current' => request()->routeIs('settings.correlatives'),
                    ],
                ],
            ],
            [
                'heading' => 'Operacion',
                'items' => [
                    [
                        'label' => 'Compras',
                        'description' => 'Solicitudes, cotizaciones y comparativas por obra.',
                        'icon' => 'clipboard-document-list',
                        'route' => 'modules.purchases',
                        'href' => route('modules.purchases'),
                        'current' => request()->routeIs('modules.purchases') || request()->routeIs('purchases.*'),
                    ],
                    ...array_map(
                        fn (PlatformModule $module): array => $this->item($module),
                        PlatformModule::businessModules(),
                    ),
                ],
            ],
        ];
    }

    /**
     * @return array<string, string|bool>
     */
    protected function item(PlatformModule $module): array
    {
        return [
            'label' => $module->label(),
            'description' => $module->description(),
            'icon' => $module->icon(),
            'route' => $module->routeName(),
            'href' => route($module->routeName()),
            'current' => $module->isNavCurrent(),
        ];
    }
}
