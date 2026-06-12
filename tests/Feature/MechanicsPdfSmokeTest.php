<?php

use App\Livewire\Mechanics\ShowMechanicalDashboard;

test('mechanical dashboard pdf preview routes respond with inline pdf', function () {
    authenticateWithCompany();

    $this->get(route('modules.mechanics'))->assertOk();

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

    Livewire::test(ShowMechanicalDashboard::class)
        ->call('openRoutePdfModal', 'mechanics.report.work-orders.pdf', 'Órdenes de trabajo')
        ->assertSet('showPdfModal', true)
        ->assertSeeHtml('iframe');
});
