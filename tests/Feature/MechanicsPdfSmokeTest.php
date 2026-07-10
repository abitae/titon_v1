<?php

use App\Livewire\Mechanics\ShowMechanicalReports;
use Livewire\Livewire;

test('mechanics dashboard renders graphical kpis and charts', function () {
    authenticateWithCompany();

    $response = $this->get(route('modules.mechanics'))
        ->assertOk()
        ->assertSee('Panel de mecánica')
        ->assertSee('Equipos por estado')
        ->assertSee('Órdenes de trabajo por estado')
        ->assertSee('data-chart-root', false);

    expect($response->getContent())->toContain('"type":"doughnut"')
        ->and($response->getContent())->toContain('data-chart-id="mech-equipment-status"');
});

test('mechanical reports page opens pdf preview in modal', function () {
    authenticateWithCompany();

    $this->get(route('mechanics.reports'))->assertOk();

    $previewRoutes = [
        'mechanics.report.equipments.pdf',
        'mechanics.report.machinery-status.pdf',
        'mechanics.report.inspections.pdf',
        'mechanics.report.preventive.pdf',
        'mechanics.report.corrective.pdf',
        'mechanics.report.work-orders.pdf',
        'mechanics.report.work-orders.by-technician.pdf',
    ];

    foreach ($previewRoutes as $routeName) {
        $response = $this->get(route($routeName, ['preview' => 1]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        expect(str_starts_with($response->getContent(), '%PDF'))->toBeTrue();
    }

    Livewire::test(ShowMechanicalReports::class)
        ->call('openMechanicsReportPdf', 'mechanics.report.work-orders.pdf', 'Ordenes de trabajo')
        ->assertSet('showPdfModal', true)
        ->assertSeeHtml('iframe');
});
