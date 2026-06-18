<?php

namespace App\Services\Navigation;

use App\Enums\PlatformModule;
use Illuminate\Support\Facades\Auth;

class AppNavigation
{
    /**
     * @return array<int, array{heading: string, items: array<int, array<string, string|bool>>}>
     */
    public function sidebarGroups(): array
    {
        $groups = [
            [
                'heading' => 'Plataforma',
                'items' => [
                    $this->item(PlatformModule::Dashboard),
                ],
            ],
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
                    [
                        'label' => 'Tipos de costo',
                        'description' => 'Clasificacion de costos para requerimientos.',
                        'icon' => 'banknotes',
                        'route' => 'settings.cost-types',
                        'href' => route('settings.cost-types'),
                        'current' => request()->routeIs('settings.cost-types'),
                    ],
                ],
            ],
        ];

        if (Auth::user()?->hasRole('Super Admin')) {
            $groups[] = [
                'heading' => 'Sitio web',
                'items' => [
                    [
                        'label' => 'Contenido',
                        'description' => 'Textos e imágenes del sitio público.',
                        'icon' => 'document-text',
                        'route' => 'admin.site-content',
                        'href' => route('admin.site-content'),
                        'current' => request()->routeIs('admin.site-content'),
                    ],
                    [
                        'label' => 'Portafolio',
                        'description' => 'Proyectos publicados en el sitio.',
                        'icon' => 'photo',
                        'route' => 'admin.showcase-projects',
                        'href' => route('admin.showcase-projects'),
                        'current' => request()->routeIs('admin.showcase-projects'),
                    ],
                    [
                        'label' => 'Mensajes',
                        'description' => 'Formulario de contacto del sitio.',
                        'icon' => 'envelope',
                        'route' => 'admin.contact-messages',
                        'href' => route('admin.contact-messages'),
                        'current' => request()->routeIs('admin.contact-messages'),
                    ],
                ],
            ];
        }

        return $groups;
    }

    /**
     * @return array<int, array<string, string|bool>>
     */
    protected function procurementItems(): array
    {
        return [
            [
                'label' => 'Requerimientos',
                'description' => 'Solicitudes, cotizaciones y comparacion por obra.',
                'icon' => 'clipboard-document-list',
                'route' => 'modules.purchases',
                'href' => route('modules.purchases'),
                'current' => request()->routeIs([
                    'modules.purchases',
                    'purchases.send-suppliers',
                    'purchases.quotations',
                    'purchases.comparison',
                    'purchases.winner',
                    'purchases.comparison.pdf',
                    'purchases.order.pdf',
                ]),
            ],
            [
                'label' => 'Ordenes de compra',
                'description' => 'Emision, conformidad y anulacion de OC.',
                'icon' => 'shopping-cart',
                'route' => 'purchases.orders',
                'href' => route('purchases.orders'),
                'current' => request()->routeIs([
                    'purchases.orders',
                    'purchases.orders.pdf',
                    'purchases.orders.pdf.preview',
                ]),
            ],
            [
                'label' => 'Cuentas por pagar',
                'description' => 'Pago de ordenes conformes y documentos CxP.',
                'icon' => 'credit-card',
                'route' => 'accounts-payable.index',
                'href' => route('accounts-payable.index'),
                'current' => request()->routeIs('accounts-payable.*'),
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
