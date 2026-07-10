<?php

use App\Services\Navigation\AppNavigation;
use Tests\Support\AuthenticatesWithCompany;

uses(AuthenticatesWithCompany::class);

beforeEach(function () {
    $this->authenticateWithCompany('Super Admin');
});

test('sidebar exposes dashboard as primary item outside groups', function () {
    $primaryItems = app(AppNavigation::class)->sidebarPrimaryItems();
    $groups = app(AppNavigation::class)->sidebarGroups();

    expect(collect($primaryItems)->pluck('label')->all())->toBe(['Dashboard'])
        ->and(collect($groups)->pluck('heading')->all())->not->toContain('Plataforma');
});

test('sidebar exposes procurement flow in order', function () {
    $groups = app(AppNavigation::class)->sidebarGroups();

    $compras = collect($groups)->firstWhere('heading', 'Compras');

    expect($compras)->not->toBeNull()
        ->and(collect($compras['items'])->pluck('label')->all())->toBe([
            'Requerimientos',
            'Ordenes de compra',
            'Cuentas por pagar',
        ]);
});

test('sidebar keeps accounts payable out of general operation modules', function () {
    $groups = app(AppNavigation::class)->sidebarGroups();

    $operacion = collect($groups)->firstWhere('heading', 'Operacion');

    expect(collect($operacion['items'])->pluck('label')->all())->not->toContain('Cuentas por pagar')
        ->and(collect($operacion['items'])->pluck('label')->all())->not->toContain('Mecanica')
        ->and(collect($operacion['items'])->pluck('label')->all())->toContain('Pagos a proveedores');
});

test('sidebar exposes mechanics module with submenus in order', function () {
    $groups = app(AppNavigation::class)->sidebarGroups();

    $mecanica = collect($groups)->firstWhere('heading', 'Mecanica');

    expect($mecanica)->not->toBeNull()
        ->and(collect($mecanica['items'])->pluck('label')->all())->toBe([
            'Panel',
            'Reportes',
            'Equipos',
            'Tipos de equipo',
            'Revisiones tecnicas',
            'Mantenimiento preventivo',
            'Mantenimiento correctivo',
            'Ordenes de trabajo',
            'Repuestos',
        ]);
});

test('sidebar exposes banks module under operation', function () {
    $groups = app(AppNavigation::class)->sidebarGroups();

    $operacion = collect($groups)->firstWhere('heading', 'Operacion');

    expect(collect($operacion['items'])->pluck('label')->all())->toContain('Bancos');
});

test('sidebar exposes pdf formats under configuration', function () {
    $groups = app(AppNavigation::class)->sidebarGroups();

    $config = collect($groups)->firstWhere('heading', 'Configuracion');

    expect(collect($config['items'])->pluck('label')->all())->toContain('Formatos PDF');
});

test('sidebar exposes roles and permissions under security', function () {
    $groups = app(AppNavigation::class)->sidebarGroups();

    $seguridad = collect($groups)->firstWhere('heading', 'Seguridad');

    expect(collect($seguridad['items'])->pluck('label')->all())->toContain('Roles', 'Permisos');
});
