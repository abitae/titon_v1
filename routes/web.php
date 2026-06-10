<?php

use App\Enums\PlatformModule;
use App\Http\Controllers\ActiveCompanyController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\MechanicsReportDownloadController;
use App\Http\Controllers\PurchaseComparisonDownloadController;
use App\Http\Controllers\ReportDownloadController;
use App\Http\Controllers\UserController;
use App\Livewire\AccountsPayable\ManageAccountsPayable;
use App\Livewire\AccountsPayable\ShowAccountsPayable;
use App\Livewire\Auditoria\Usuarios\ManageUserAudits;
use App\Livewire\Contracts\ManageSupplierContracts;
use App\Livewire\Dashboard\ShowDashboard;
use App\Livewire\Documents\DocumentTimeline;
use App\Livewire\Documents\ManageInbox;
use App\Livewire\Documents\ManageOutbox;
use App\Livewire\Documents\ProjectDocuments;
use App\Livewire\Documents\ShowDocument;
use App\Livewire\Mechanics\ManageFleetCorrectiveMaintenances;
use App\Livewire\Mechanics\ManageFleetEquipments;
use App\Livewire\Mechanics\ManageFleetPreventiveMaintenances;
use App\Livewire\Mechanics\ManageFleetSpareParts;
use App\Livewire\Mechanics\ManageFleetTechnicalInspections;
use App\Livewire\Mechanics\ManageFleetWorkOrders;
use App\Livewire\Mechanics\ShowMechanicalDashboard;
use App\Livewire\Orders\RecordOrderConformity;
use App\Livewire\Payments\ManagePaymentSchedules;
use App\Livewire\Payments\ManageSupplierPayments;
use App\Livewire\Projects\ManageProjects;
use App\Livewire\Purchases\ManagePurchaseOrders;
use App\Livewire\Purchases\ManagePurchaseRequests;
use App\Livewire\Purchases\ManageSupplierQuotations;
use App\Livewire\Purchases\SelectWinningQuotation;
use App\Livewire\Purchases\ShowQuotationComparison;
use App\Livewire\Requirements\SendRequirementToSuppliers;
use App\Livewire\Settings\ManageCatalogs;
use App\Livewire\Settings\ManageCorrelativeFormats;
use App\Livewire\Settings\ManageCostTypes;
use App\Livewire\Suppliers\ManageSuppliers;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'active.company.context'])->group(function () {
    Route::post('active-company', [ActiveCompanyController::class, 'store'])->name('active-company.store');

    Route::resource('companies', CompanyController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');

    Route::middleware('active.company')->group(function () {
        Route::get('dashboard', ShowDashboard::class)->name('dashboard');
        Route::get('dashboard/executive-report.pdf', [ReportDownloadController::class, 'dashboard'])
            ->name('reports.dashboard.pdf');
        Route::get('auditoria/usuarios', ManageUserAudits::class)
            ->middleware('permission:audits.ver')
            ->name('audits.users');
        Route::get('settings/catalogs', ManageCatalogs::class)
            ->middleware('permission:catalogs.ver')
            ->name('settings.catalogs');
        Route::get('settings/correlatives', ManageCorrelativeFormats::class)
            ->middleware('permission:catalogs.ver')
            ->name('settings.correlatives');
        Route::get('settings/cost-types', ManageCostTypes::class)
            ->middleware('permission:catalogs.ver')
            ->name('settings.cost-types');

        Route::get('projects', ManageProjects::class)
            ->middleware('permission:projects.ver')
            ->name('modules.projects');

        Route::get('purchases/requests', ManagePurchaseRequests::class)
            ->middleware('permission:purchases.ver')
            ->name('modules.purchases');
        Route::get('purchases/requests/{purchaseRequest}/send-suppliers', SendRequirementToSuppliers::class)
            ->middleware('permission:purchases.aprobar')
            ->name('purchases.send-suppliers');
        Route::get('purchases/requests/{purchaseRequest}/quotations', ManageSupplierQuotations::class)
            ->middleware('permission:purchases.ver')
            ->name('purchases.quotations');
        Route::get('purchases/quotations/{supplierQuotation}/pdf', [PurchaseComparisonDownloadController::class, 'quotationPreview'])
            ->middleware('permission:purchases.ver')
            ->name('purchases.quotations.pdf');
        Route::get('purchases/requests/{purchaseRequest}/comparison', ShowQuotationComparison::class)
            ->middleware('permission:purchases.ver')
            ->name('purchases.comparison');
        Route::get('purchases/requests/{purchaseRequest}/winner', SelectWinningQuotation::class)
            ->middleware('permission:purchases.aprobar')
            ->name('purchases.winner');
        Route::get('purchases/requests/{purchaseRequest}/comparison.pdf', [PurchaseComparisonDownloadController::class, 'comparison'])
            ->middleware('permission:purchases.exportar')
            ->name('purchases.comparison.pdf');
        Route::get('purchases/requests/{purchaseRequest}/order.pdf', [PurchaseComparisonDownloadController::class, 'purchaseOrder'])
            ->middleware('permission:purchases.exportar')
            ->name('purchases.order.pdf');
        Route::get('purchase-orders', ManagePurchaseOrders::class)
            ->middleware('permission:purchases.ver')
            ->name('purchases.orders');
        Route::get('purchase-orders/{purchaseOrder}/pdf', [PurchaseComparisonDownloadController::class, 'order'])
            ->middleware('permission:purchases.exportar')
            ->name('purchases.orders.pdf');
        Route::get('purchase-orders/{purchaseOrder}/conformity', RecordOrderConformity::class)
            ->middleware('permission:purchases.aprobar')
            ->name('purchases.orders.conformity');

        Route::get('accounts-payable', ManageAccountsPayable::class)
            ->middleware('permission:payments.ver')
            ->name('accounts-payable.index');
        Route::get('accounts-payable/{accountsPayable}', ShowAccountsPayable::class)
            ->middleware('permission:payments.ver')
            ->name('accounts-payable.show');

        Route::redirect('payments', '/accounts-payable')->name('modules.payments.redirect');

        Route::get('suppliers', ManageSuppliers::class)
            ->middleware('permission:suppliers.ver')
            ->name('modules.suppliers');

        Route::get(PlatformModule::Documents->slug(), ManageInbox::class)
            ->middleware('permission:documents.ver')
            ->name(PlatformModule::Documents->routeName());
        Route::get('documents/outbox', ManageOutbox::class)
            ->middleware('permission:documents.ver')
            ->name('documents.outbox');
        Route::get('documents/projects', ProjectDocuments::class)
            ->middleware('permission:documents.ver')
            ->name('documents.projects');
        Route::get('documents/{document}', ShowDocument::class)
            ->middleware('permission:documents.ver')
            ->name('documents.show');
        Route::get('documents/{document}/timeline', DocumentTimeline::class)
            ->middleware('permission:documents.ver')
            ->name('documents.timeline');

        Route::get(PlatformModule::Contracts->slug(), ManageSupplierContracts::class)
            ->middleware('permission:contracts.ver')
            ->name(PlatformModule::Contracts->routeName());
        Route::get('contracts/{supplierContract}/pdf', [PurchaseComparisonDownloadController::class, 'contract'])
            ->middleware('permission:contracts.exportar')
            ->name('contracts.pdf');

        Route::get(PlatformModule::Payments->slug(), ManageSupplierPayments::class)
            ->middleware('permission:payments.ver')
            ->name(PlatformModule::Payments->routeName());
        Route::get('contracts/{supplierContract}/payment-schedules', ManagePaymentSchedules::class)
            ->middleware('permission:payments.ver')
            ->name('payments.schedules');

        Route::prefix('mecanica')->group(function () {
            Route::get('/', ShowMechanicalDashboard::class)->middleware('permission:mecanica.ver')->name('modules.mechanics');
            Route::get('/equipos', ManageFleetEquipments::class)->middleware('permission:equipos.ver')->name('mechanics.equipments');
            Route::get('/revisiones', ManageFleetTechnicalInspections::class)->middleware('permission:revisiones.ver')->name('mechanics.inspections');
            Route::get('/preventivo', ManageFleetPreventiveMaintenances::class)->middleware('permission:mantenimientos.ver')->name('mechanics.preventive');
            Route::get('/correctivo', ManageFleetCorrectiveMaintenances::class)->middleware('permission:mantenimientos.ver')->name('mechanics.corrective');
            Route::get('/ordenes-trabajo', ManageFleetWorkOrders::class)->middleware('permission:mantenimientos.ver')->name('mechanics.work-orders');
            Route::get('/repuestos', ManageFleetSpareParts::class)->middleware('permission:mecanica.ver')->name('mechanics.spare-parts');
            Route::get('/reportes/equipos.pdf', [MechanicsReportDownloadController::class, 'equipmentsPdf'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.equipments.pdf');
            Route::get('/reportes/equipos.xlsx', [MechanicsReportDownloadController::class, 'equipmentsExcel'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.equipments.excel');

            Route::get('/reportes/estado-maquinaria.pdf', [MechanicsReportDownloadController::class, 'machineryStatusPdf'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.machinery-status.pdf');
            Route::get('/reportes/estado-maquinaria.xlsx', [MechanicsReportDownloadController::class, 'machineryStatusExcel'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.machinery-status.excel');
            Route::get('/reportes/revisiones.pdf', [MechanicsReportDownloadController::class, 'inspectionsPdf'])
                ->middleware('permission:revisiones.exportar')->name('mechanics.report.inspections.pdf');
            Route::get('/reportes/revisiones.xlsx', [MechanicsReportDownloadController::class, 'inspectionsExcel'])
                ->middleware('permission:revisiones.exportar')->name('mechanics.report.inspections.excel');
            Route::get('/reportes/preventivo.pdf', [MechanicsReportDownloadController::class, 'preventivePdf'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.preventive.pdf');
            Route::get('/reportes/preventivo.xlsx', [MechanicsReportDownloadController::class, 'preventiveExcel'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.preventive.excel');
            Route::get('/reportes/correctivo.pdf', [MechanicsReportDownloadController::class, 'correctivePdf'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.corrective.pdf');
            Route::get('/reportes/correctivo.xlsx', [MechanicsReportDownloadController::class, 'correctiveExcel'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.corrective.excel');
            Route::get('/reportes/ordenes-trabajo.pdf', [MechanicsReportDownloadController::class, 'workOrdersPdf'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.work-orders.pdf');
            Route::get('/reportes/ordenes-trabajo.xlsx', [MechanicsReportDownloadController::class, 'workOrdersExcel'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.work-orders.excel');
            Route::get('/reportes/ot-por-tecnico.pdf', [MechanicsReportDownloadController::class, 'workOrdersGroupedTechnicianPdf'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.work-orders.by-technician.pdf');
            Route::get('/reportes/ot-por-tecnico.xlsx', [MechanicsReportDownloadController::class, 'workOrdersGroupedTechnicianExcel'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.work-orders.by-technician.excel');
            Route::get('/reportes/ot-por-obra.pdf', [MechanicsReportDownloadController::class, 'workOrdersGroupedProjectPdf'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.work-orders.by-project.pdf');
            Route::get('/reportes/ot-por-obra.xlsx', [MechanicsReportDownloadController::class, 'workOrdersGroupedProjectExcel'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.work-orders.by-project.excel');
            Route::get('/reportes/ot-por-equipo.pdf', [MechanicsReportDownloadController::class, 'workOrdersGroupedEquipmentPdf'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.work-orders.by-equipment.pdf');
            Route::get('/reportes/ot-por-equipo.xlsx', [MechanicsReportDownloadController::class, 'workOrdersGroupedEquipmentExcel'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.work-orders.by-equipment.excel');
            Route::get('/reportes/ot-vencidas.pdf', [MechanicsReportDownloadController::class, 'workOrdersOverduePdf'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.work-orders.overdue.pdf');
            Route::get('/reportes/ot-vencidas.xlsx', [MechanicsReportDownloadController::class, 'workOrdersOverdueExcel'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.work-orders.overdue.excel');
            Route::get('/reportes/ot-tipos.pdf', [MechanicsReportDownloadController::class, 'workOrdersTypesPdf'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.work-orders.types.pdf');
            Route::get('/reportes/ot-tipos.xlsx', [MechanicsReportDownloadController::class, 'workOrdersTypesExcel'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.work-orders.types.excel');
            Route::get('/reportes/ot-costos.pdf', [MechanicsReportDownloadController::class, 'workOrdersCostsPdf'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.work-orders.costs.pdf');
            Route::get('/reportes/ot-costos.xlsx', [MechanicsReportDownloadController::class, 'workOrdersCostsExcel'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.work-orders.costs.excel');
            Route::get('/reportes/costos-mantenimiento.pdf', [MechanicsReportDownloadController::class, 'maintenanceCostsPdf'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.maintenance-costs.pdf');
            Route::get('/reportes/costos-mantenimiento.xlsx', [MechanicsReportDownloadController::class, 'maintenanceCostsExcel'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.maintenance-costs.excel');
            Route::get('/reportes/repuestos-consumidos.pdf', [MechanicsReportDownloadController::class, 'consumedSparesPdf'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.consumed-spares.pdf');
            Route::get('/reportes/repuestos-consumidos.xlsx', [MechanicsReportDownloadController::class, 'consumedSparesExcel'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.consumed-spares.excel');
            Route::get('/reportes/equipos-por-obra.pdf', [MechanicsReportDownloadController::class, 'equipmentByProjectPdf'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.equipment-by-project.pdf');
            Route::get('/reportes/equipos-por-obra.xlsx', [MechanicsReportDownloadController::class, 'equipmentByProjectExcel'])
                ->middleware('permission:mecanica.exportar')->name('mechanics.report.equipment-by-project.excel');
        });
    });
});

require __DIR__.'/settings.php';
