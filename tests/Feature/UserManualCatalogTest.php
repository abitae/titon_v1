<?php

use App\Services\UserManuals\UserManualCatalog;
use Tests\Support\AuthenticatesWithCompany;

uses(AuthenticatesWithCompany::class);

test('user manual catalog resolves manuals for internal module routes', function (string $routeName, string $expectedModule) {
    $manual = app(UserManualCatalog::class)->forRoute($routeName);

    expect($manual)->not->toBeNull()
        ->and($manual['module'])->toBe($expectedModule)
        ->and($manual['purpose'])->toBeString()->not->toBeEmpty()
        ->and($manual['sections'])->toHaveCount(5);
})->with([
    'dashboard' => ['dashboard', 'Dashboard'],
    'requirements' => ['modules.purchases', 'Compras / Requerimientos'],
    'send suppliers' => ['purchases.send-suppliers', 'Compras / Requerimientos'],
    'quotations' => ['purchases.quotations', 'Compras / Cotizaciones'],
    'comparison' => ['purchases.comparison', 'Compras / Comparativo'],
    'winner' => ['purchases.winner', 'Compras / Comparativo'],
    'orders' => ['purchases.orders', 'Compras / Ordenes de compra'],
    'accounts payable index' => ['accounts-payable.index', 'Cuentas por pagar'],
    'accounts payable show' => ['accounts-payable.show', 'Cuentas por pagar'],
    'documents inbox' => ['modules.documents', 'Documentos'],
    'documents outbox' => ['documents.outbox', 'Documentos'],
    'documents projects' => ['documents.projects', 'Documentos'],
    'document detail' => ['documents.show', 'Documentos'],
    'document timeline' => ['documents.timeline', 'Documentos'],
    'projects' => ['modules.projects', 'Obras'],
    'suppliers' => ['modules.suppliers', 'Proveedores'],
    'contracts' => ['modules.contracts', 'Contratos'],
    'payment schedules' => ['payments.schedules', 'Contratos'],
    'payments' => ['modules.payments', 'Pagos a proveedores'],
    'banks' => ['modules.banks', 'Bancos'],
    'warehouse' => ['modules.warehouse', 'Almacen'],
    'mechanics panel' => ['modules.mechanics', 'Mecanica / Panel'],
    'mechanics reports' => ['mechanics.reports', 'Mecanica / Reportes'],
    'mechanics equipments' => ['mechanics.equipments', 'Mecanica / Equipos'],
    'mechanics equipment types' => ['mechanics.equipment-types', 'Mecanica / Equipos'],
    'mechanics inspections' => ['mechanics.inspections', 'Mecanica / Revisiones tecnicas'],
    'mechanics preventive' => ['mechanics.preventive', 'Mecanica / Mantenimiento preventivo y correctivo'],
    'mechanics corrective' => ['mechanics.corrective', 'Mecanica / Mantenimiento preventivo y correctivo'],
    'mechanics work orders' => ['mechanics.work-orders', 'Mecanica / Ordenes de trabajo'],
    'mechanics spare parts' => ['mechanics.spare-parts', 'Mecanica / Repuestos'],
    'companies' => ['companies.index', 'Seguridad'],
    'users' => ['users.index', 'Seguridad'],
    'roles' => ['security.roles', 'Seguridad'],
    'permissions' => ['security.permissions', 'Seguridad'],
    'audits' => ['audits.users', 'Seguridad'],
    'catalogs' => ['settings.catalogs', 'Configuracion'],
    'correlatives' => ['settings.correlatives', 'Configuracion'],
    'pdf formats' => ['settings.pdf-formats', 'Configuracion'],
    'cost types' => ['settings.cost-types', 'Configuracion'],
    'site content' => ['admin.site-content', 'Sitio web'],
    'showcase projects' => ['admin.showcase-projects', 'Sitio web'],
    'contact messages' => ['admin.contact-messages', 'Sitio web'],
]);

test('user manual catalog applies group fallbacks and ignores unrelated routes', function () {
    $catalog = app(UserManualCatalog::class);

    expect($catalog->forRoute('mechanics.report.work-orders.pdf')['module'])->toBe('Mecanica / Reportes')
        ->and($catalog->forRoute('settings.pdf-formats.preview')['module'])->toBe('Configuracion')
        ->and($catalog->forRoute('frontend.home'))->toBeNull()
        ->and($catalog->forRoute(null))->toBeNull();
});

test('authenticated layout renders the floating user manual button', function () {
    $this->authenticateWithCompany('Super Admin');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-test="user-manual-widget"', false)
        ->assertSee('data-test="user-manual-button"', false)
        ->assertSee('Manual de usuario - Dashboard')
        ->assertSee('Presentar una lectura ejecutiva');
});
