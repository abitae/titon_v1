<?php

use App\Services\Navigation\AppNavigation;

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

    expect(collect($operacion['items'])->pluck('label')->all())->not->toContain('Cuentas por pagar');
});
