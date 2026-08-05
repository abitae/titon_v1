<?php

namespace App\Http\Controllers;

use App\Concerns\AppliesExportCorrelationStamp;
use App\Exports\FleetEquipmentsExport;
use App\Exports\MechanicsFlatExcelExport;
use App\Models\FleetEquipment;
use App\Reports\Mechanics\FleetEquipmentHistoryPdfReport;
use App\Reports\Mechanics\FleetEquipmentsPdfReport;
use App\Reports\Mechanics\GenericMechanicsPdfReport;
use App\Services\Audit\UserAuditLogger;
use App\Services\Mechanics\FleetWorkOrderBoardQuery;
use App\Services\Mechanics\MechanicsReportDatasets;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MechanicsReportDownloadController extends Controller
{
    use AppliesExportCorrelationStamp;

    public function __construct(
        protected MechanicsReportDatasets $reportDatasets,
    ) {}

    public function equipmentsPdf(Request $request, UserAuditLogger $userAuditLogger, FleetEquipmentsPdfReport $fleetEquipmentsPdfReport): StreamedResponse|Response
    {
        $this->authorizeMechanicsExport();

        $pdf = $fleetEquipmentsPdfReport->build(auth()->user());

        return $this->deliverPdf($request, $pdf, 'equipos-maquinarias.pdf', $userAuditLogger, 'equipos_pdf', 'PDF listado de equipos.');
    }

    public function equipmentsExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();

        $rows = FleetEquipment::query()->with(['workProject', 'responsibleUser'])->orderBy('internal_code')->get();

        $this->logMechanicsExport($userAuditLogger, 'equipos_excel', 'Excel listado de equipos.');

        return Excel::download(new FleetEquipmentsExport($rows), $this->stampedExportFilename('equipos-maquinarias.xlsx'));
    }

    public function equipmentHistoryPdf(
        Request $request,
        FleetEquipment $fleetEquipment,
        UserAuditLogger $userAuditLogger,
        FleetEquipmentHistoryPdfReport $fleetEquipmentHistoryPdfReport,
    ): StreamedResponse|Response {
        $this->authorizeMechanicsExport();

        $pdf = $fleetEquipmentHistoryPdfReport->build(auth()->user(), $fleetEquipment);
        $filename = 'historial-equipo-'.$fleetEquipment->internal_code.'.pdf';

        return $this->deliverPdf($request, $pdf, $filename, $userAuditLogger, 'equipo_historial_pdf', 'PDF historial de equipo.');
    }

    public function machineryStatusPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse|Response
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->machineryStatus();
        $pdf = $genericMechanicsPdfReport->build(
            auth()->user(),
            'Estado de maquinaria',
            'Estado de maquinaria',
            $dataset['headings'],
            $dataset['rows'],
            $dataset['summary'],
        );

        return $this->deliverPdf($request, $pdf, 'estado-maquinaria.pdf', $userAuditLogger, 'estado_maquinaria_pdf', 'PDF estado de maquinaria.');
    }

    public function machineryStatusExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->machineryStatus();
        $this->logMechanicsExport($userAuditLogger, 'estado_maquinaria_excel', 'Excel estado de maquinaria.');

        return Excel::download(new MechanicsFlatExcelExport($dataset['headings'], $dataset['rows']), $this->stampedExportFilename('estado-maquinaria.xlsx'));
    }

    public function inspectionsPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse|Response
    {
        $this->authorizeInspectionsExport();
        $dataset = $this->reportDatasets->inspections();
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Revisiones tecnicas', 'Revisiones tecnicas', $dataset['headings'], $dataset['rows']);

        return $this->deliverPdf($request, $pdf, 'revisiones-tecnicas.pdf', $userAuditLogger, 'revisiones_pdf', 'PDF revisiones tecnicas.');
    }

    public function inspectionsExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->inspections();
        $this->logMechanicsExport($userAuditLogger, 'revisiones_excel', 'Excel revisiones tecnicas.');

        return Excel::download(new MechanicsFlatExcelExport($dataset['headings'], $dataset['rows']), $this->stampedExportFilename('revisiones-tecnicas.xlsx'));
    }

    public function preventivePdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse|Response
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->preventive();
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Mantenimiento preventivo', 'Mantenimientos preventivos', $dataset['headings'], $dataset['rows']);

        return $this->deliverPdf($request, $pdf, 'mantenimiento-preventivo.pdf', $userAuditLogger, 'preventivo_pdf', 'PDF mantenimiento preventivo.');
    }

    public function preventiveExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->preventive();
        $this->logMechanicsExport($userAuditLogger, 'preventivo_excel', 'Excel mantenimiento preventivo.');

        return Excel::download(new MechanicsFlatExcelExport($dataset['headings'], $dataset['rows']), $this->stampedExportFilename('mantenimiento-preventivo.xlsx'));
    }

    public function correctivePdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse|Response
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->corrective();
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Mantenimiento correctivo', 'Mantenimientos correctivos', $dataset['headings'], $dataset['rows']);

        return $this->deliverPdf($request, $pdf, 'mantenimiento-correctivo.pdf', $userAuditLogger, 'correctivo_pdf', 'PDF mantenimiento correctivo.');
    }

    public function correctiveExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->corrective();
        $this->logMechanicsExport($userAuditLogger, 'correctivo_excel', 'Excel mantenimiento correctivo.');

        return Excel::download(new MechanicsFlatExcelExport($dataset['headings'], $dataset['rows']), $this->stampedExportFilename('mantenimiento-correctivo.xlsx'));
    }

    public function workOrdersPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse|Response
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->workOrdersFlat($this->filtersFromRequest($request));
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Ordenes de trabajo mecanicas', 'Ordenes de trabajo mecanicas (detalle)', $dataset['headings'], $dataset['rows']);

        return $this->deliverPdf($request, $pdf, 'ordenes-trabajo-mecanicas.pdf', $userAuditLogger, 'ot_pdf', 'PDF ordenes de trabajo filtradas.');
    }

    public function workOrdersExcel(Request $request, UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->workOrdersFlat($this->filtersFromRequest($request));
        $this->logMechanicsExport($userAuditLogger, 'ot_excel', 'Excel ordenes de trabajo filtradas.');

        return Excel::download(new MechanicsFlatExcelExport($dataset['headings'], $dataset['rows']), $this->stampedExportFilename('ordenes-trabajo-mecanicas.xlsx'));
    }

    public function workOrdersGroupedTechnicianPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse|Response
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->workOrdersGroupedByTechnician($this->filtersFromRequest($request));
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'OT por tecnico', 'OT por tecnico', $dataset['headings'], $dataset['rows']);

        return $this->deliverPdf($request, $pdf, 'ot-por-tecnico.pdf', $userAuditLogger, 'ot_por_tecnico_pdf', 'PDF OT agrupadas por tecnico.');
    }

    public function workOrdersGroupedTechnicianExcel(Request $request, UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->workOrdersGroupedByTechnician($this->filtersFromRequest($request));
        $this->logMechanicsExport($userAuditLogger, 'ot_por_tecnico_excel', 'Excel OT agrupadas por tecnico.');

        return Excel::download(new MechanicsFlatExcelExport($dataset['headings'], $dataset['rows']), $this->stampedExportFilename('ot-por-tecnico.xlsx'));
    }

    public function workOrdersGroupedProjectPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse|Response
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->workOrdersGroupedByProject($this->filtersFromRequest($request));
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'OT por obra', 'OT por obra', $dataset['headings'], $dataset['rows']);

        return $this->deliverPdf($request, $pdf, 'ot-por-obra.pdf', $userAuditLogger, 'ot_por_obra_pdf', 'PDF OT agrupadas por obra.');
    }

    public function workOrdersGroupedProjectExcel(Request $request, UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->workOrdersGroupedByProject($this->filtersFromRequest($request));
        $this->logMechanicsExport($userAuditLogger, 'ot_por_obra_excel', 'Excel OT agrupadas por obra.');

        return Excel::download(new MechanicsFlatExcelExport($dataset['headings'], $dataset['rows']), $this->stampedExportFilename('ot-por-obra.xlsx'));
    }

    public function workOrdersGroupedEquipmentPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse|Response
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->workOrdersGroupedByEquipment($this->filtersFromRequest($request));
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'OT por equipo', 'OT por equipo', $dataset['headings'], $dataset['rows']);

        return $this->deliverPdf($request, $pdf, 'ot-por-equipo.pdf', $userAuditLogger, 'ot_por_equipo_pdf', 'PDF OT agrupadas por equipo.');
    }

    public function workOrdersGroupedEquipmentExcel(Request $request, UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->workOrdersGroupedByEquipment($this->filtersFromRequest($request));
        $this->logMechanicsExport($userAuditLogger, 'ot_por_equipo_excel', 'Excel OT agrupadas por equipo.');

        return Excel::download(new MechanicsFlatExcelExport($dataset['headings'], $dataset['rows']), $this->stampedExportFilename('ot-por-equipo.xlsx'));
    }

    public function workOrdersOverduePdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse|Response
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->workOrdersOverdueFlat($this->filtersFromRequest($request));
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'OT vencidas', 'Ordenes vencidas (programacion)', $dataset['headings'], $dataset['rows']);

        return $this->deliverPdf($request, $pdf, 'ot-vencidas.pdf', $userAuditLogger, 'ot_vencidas_pdf', 'PDF OT vencidas.');
    }

    public function workOrdersOverdueExcel(Request $request, UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->workOrdersOverdueFlat($this->filtersFromRequest($request));
        $this->logMechanicsExport($userAuditLogger, 'ot_vencidas_excel', 'Excel OT vencidas.');

        return Excel::download(new MechanicsFlatExcelExport($dataset['headings'], $dataset['rows']), $this->stampedExportFilename('ot-vencidas.xlsx'));
    }

    public function workOrdersTypesPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse|Response
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->workOrdersTypes($this->filtersFromRequest($request));
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'OT preventivo vs correctivo', 'Conteo OT por tipo', $dataset['headings'], $dataset['rows']);

        return $this->deliverPdf($request, $pdf, 'ot-preventivo-correctivo.pdf', $userAuditLogger, 'ot_tipos_pdf', 'PDF conteo OT por tipo.');
    }

    public function workOrdersTypesExcel(Request $request, UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->workOrdersTypes($this->filtersFromRequest($request));
        $this->logMechanicsExport($userAuditLogger, 'ot_tipos_excel', 'Excel conteo OT por tipo.');

        return Excel::download(new MechanicsFlatExcelExport($dataset['headings'], $dataset['rows']), $this->stampedExportFilename('ot-preventivo-correctivo.xlsx'));
    }

    public function workOrdersCostsPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse|Response
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->workOrdersCosts($this->filtersFromRequest($request));
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Costos por OT', 'Costos por OT', $dataset['headings'], $dataset['rows'], $dataset['summary']);

        return $this->deliverPdf($request, $pdf, 'ot-costos.pdf', $userAuditLogger, 'ot_costos_pdf', 'PDF costos OT filtradas.');
    }

    public function workOrdersCostsExcel(Request $request, UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->workOrdersCosts($this->filtersFromRequest($request));
        $this->logMechanicsExport($userAuditLogger, 'ot_costos_excel', 'Excel costos OT filtradas.');

        return Excel::download(new MechanicsFlatExcelExport($dataset['headings'], $dataset['rows']), $this->stampedExportFilename('ot-costos.xlsx'));
    }

    public function maintenanceCostsPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse|Response
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->maintenanceCosts();
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Costos de mantenimiento', 'Costos de mantenimiento', $dataset['headings'], $dataset['rows']);

        return $this->deliverPdf($request, $pdf, 'costos-mantenimiento.pdf', $userAuditLogger, 'costos_pdf', 'PDF costos mantenimiento.');
    }

    public function maintenanceCostsExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->maintenanceCosts();
        $this->logMechanicsExport($userAuditLogger, 'costos_excel', 'Excel costos mantenimiento.');

        return Excel::download(new MechanicsFlatExcelExport($dataset['headings'], $dataset['rows']), $this->stampedExportFilename('costos-mantenimiento.xlsx'));
    }

    public function consumedSparesPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse|Response
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->consumedSpares();
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Repuestos consumidos', 'Repuestos consumidos', $dataset['headings'], $dataset['rows']);

        return $this->deliverPdf($request, $pdf, 'repuestos-consumidos.pdf', $userAuditLogger, 'repuestos_consumidos_pdf', 'PDF repuestos consumidos.');
    }

    public function consumedSparesExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->consumedSpares();
        $this->logMechanicsExport($userAuditLogger, 'repuestos_consumidos_excel', 'Excel repuestos consumidos.');

        return Excel::download(new MechanicsFlatExcelExport($dataset['headings'], $dataset['rows']), $this->stampedExportFilename('repuestos-consumidos.xlsx'));
    }

    public function equipmentByProjectPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse|Response
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->equipmentByProject();
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Equipos por obra', 'Equipos por obra', $dataset['headings'], $dataset['rows']);

        return $this->deliverPdf($request, $pdf, 'equipos-por-obra.pdf', $userAuditLogger, 'equipos_por_obra_pdf', 'PDF equipos por obra.');
    }

    public function equipmentByProjectExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        $dataset = $this->reportDatasets->equipmentByProject();
        $this->logMechanicsExport($userAuditLogger, 'equipos_por_obra_excel', 'Excel equipos por obra.');

        return Excel::download(new MechanicsFlatExcelExport($dataset['headings'], $dataset['rows']), $this->stampedExportFilename('equipos-por-obra.xlsx'));
    }

    protected function authorizeMechanicsExport(): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless(auth()->user()->can('mecanica.exportar'), 403);
    }

    protected function authorizeInspectionsExport(): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless(
            auth()->user()->can('revisiones.exportar') || auth()->user()->can('mecanica.exportar'),
            403,
        );
    }

    protected function deliverPdf(
        Request $request,
        string $binary,
        string $filename,
        UserAuditLogger $userAuditLogger,
        string $auditKey,
        string $auditObservation,
    ): StreamedResponse|Response {
        if ($request->boolean('preview')) {
            return $this->inlinePdfResponse($binary, $filename);
        }

        $this->logMechanicsExport($userAuditLogger, $auditKey, $auditObservation);

        return $this->streamPdf($binary, $filename);
    }

    protected function inlinePdfResponse(string $binary, string $filename): Response
    {
        $safeName = (string) preg_replace('/[^a-zA-Z0-9._-]+/', '-', $filename);

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$safeName.'"',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    protected function logMechanicsExport(UserAuditLogger $userAuditLogger, string $key, string $observation): void
    {
        $userAuditLogger->log(
            action: str_contains($key, '_pdf') ? 'exportacion_pdf' : 'exportacion_excel',
            module: 'Mecanica',
            auditable: auth()->user(),
            newValues: ['reporte' => $key],
            observation: $observation,
        );
    }

    protected function streamPdf(string $binary, string $filename): StreamedResponse
    {
        return response()->streamDownload(static function () use ($binary): void {
            echo $binary;
        }, $this->stampedExportFilename($filename), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function filtersFromRequest(Request $request): array
    {
        $filters = FleetWorkOrderBoardQuery::filtersFromRequest($request);
        $filters['sort'] = $request->input('sort', 'issued_at');
        $filters['dir'] = $request->input('dir', 'desc');

        return $filters;
    }
}
