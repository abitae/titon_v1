<?php

namespace App\Http\Controllers;

use App\Concerns\AppliesExportCorrelationStamp;
use App\Enums\FleetSparePartMovementDirection;
use App\Exports\FleetEquipmentsExport;
use App\Exports\MechanicsFlatExcelExport;
use App\Models\FleetCorrectiveMaintenance;
use App\Models\FleetEquipment;
use App\Models\FleetPreventiveMaintenance;
use App\Models\FleetSparePartMovement;
use App\Models\FleetTechnicalInspection;
use App\Models\FleetWorkOrder;
use App\Reports\Mechanics\FleetEquipmentsPdfReport;
use App\Reports\Mechanics\GenericMechanicsPdfReport;
use App\Services\Audit\UserAuditLogger;
use App\Services\Mechanics\FleetWorkOrderBoardQuery;
use App\Services\Mechanics\FleetWorkOrderExportDatasets;
use App\Services\Mechanics\MechanicalCostAnalytics;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MechanicsReportDownloadController extends Controller
{
    use AppliesExportCorrelationStamp;

    public function equipmentsPdf(UserAuditLogger $userAuditLogger, FleetEquipmentsPdfReport $fleetEquipmentsPdfReport): StreamedResponse
    {
        $this->authorizeMechanicsExport();

        $pdf = $fleetEquipmentsPdfReport->build(auth()->user());

        $this->logMechanicsExport($userAuditLogger, 'equipos_pdf', 'PDF listado de equipos.');

        return response()->streamDownload(static function () use ($pdf): void {
            echo $pdf;
        }, $this->stampedExportFilename('equipos-maquinarias.pdf'), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function equipmentsExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();

        $rows = FleetEquipment::query()->with(['workProject', 'responsibleUser'])->orderBy('internal_code')->get();

        $this->logMechanicsExport($userAuditLogger, 'equipos_excel', 'Excel listado de equipos.');

        return Excel::download(new FleetEquipmentsExport($rows), $this->stampedExportFilename('equipos-maquinarias.xlsx'));
    }

    public function machineryStatusPdf(UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse
    {
        $this->authorizeMechanicsExport();
        [$summaryLines, $headings, $rows] = $this->machineryStatusDataset();
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Estado de maquinaria', 'Estado de maquinaria', $headings, $rows, $summaryLines);
        $this->logMechanicsExport($userAuditLogger, 'estado_maquinaria_pdf', 'PDF estado de maquinaria.');

        return $this->streamPdf($pdf, 'estado-maquinaria.pdf');
    }

    public function machineryStatusExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        [, $headings, $rows] = $this->machineryStatusDataset();
        $this->logMechanicsExport($userAuditLogger, 'estado_maquinaria_excel', 'Excel estado de maquinaria.');

        return Excel::download(new MechanicsFlatExcelExport($headings, $rows), $this->stampedExportFilename('estado-maquinaria.xlsx'));
    }

    public function inspectionsPdf(UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = $this->inspectionsDataset();
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Revisiones tecnicas', 'Revisiones tecnicas', $headings, $rows);
        $this->logMechanicsExport($userAuditLogger, 'revisiones_pdf', 'PDF revisiones tecnicas.');

        return $this->streamPdf($pdf, 'revisiones-tecnicas.pdf');
    }

    public function inspectionsExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = $this->inspectionsDataset();
        $this->logMechanicsExport($userAuditLogger, 'revisiones_excel', 'Excel revisiones tecnicas.');

        return Excel::download(new MechanicsFlatExcelExport($headings, $rows), $this->stampedExportFilename('revisiones-tecnicas.xlsx'));
    }

    public function preventivePdf(UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = $this->preventiveDataset();
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Mantenimiento preventivo', 'Mantenimientos preventivos', $headings, $rows);
        $this->logMechanicsExport($userAuditLogger, 'preventivo_pdf', 'PDF mantenimiento preventivo.');

        return $this->streamPdf($pdf, 'mantenimiento-preventivo.pdf');
    }

    public function preventiveExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = $this->preventiveDataset();
        $this->logMechanicsExport($userAuditLogger, 'preventivo_excel', 'Excel mantenimiento preventivo.');

        return Excel::download(new MechanicsFlatExcelExport($headings, $rows), $this->stampedExportFilename('mantenimiento-preventivo.xlsx'));
    }

    public function correctivePdf(UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = $this->correctiveDataset();
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Mantenimiento correctivo', 'Mantenimientos correctivos', $headings, $rows);
        $this->logMechanicsExport($userAuditLogger, 'correctivo_pdf', 'PDF mantenimiento correctivo.');

        return $this->streamPdf($pdf, 'mantenimiento-correctivo.pdf');
    }

    public function correctiveExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = $this->correctiveDataset();
        $this->logMechanicsExport($userAuditLogger, 'correctivo_excel', 'Excel mantenimiento correctivo.');

        return Excel::download(new MechanicsFlatExcelExport($headings, $rows), $this->stampedExportFilename('mantenimiento-correctivo.xlsx'));
    }

    public function workOrdersPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = FleetWorkOrderExportDatasets::flatCostDetail((clone $this->workOrdersFilteredQuery($request)));
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Ordenes de trabajo mecanicas', 'Ordenes de trabajo mecanicas (detalle)', $headings, $rows);
        $this->logMechanicsExport($userAuditLogger, 'ot_pdf', 'PDF ordenes de trabajo filtradas.');

        return $this->streamPdf($pdf, 'ordenes-trabajo-mecanicas.pdf');
    }

    public function workOrdersExcel(Request $request, UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = FleetWorkOrderExportDatasets::flatCostDetail((clone $this->workOrdersFilteredQuery($request)));
        $this->logMechanicsExport($userAuditLogger, 'ot_excel', 'Excel ordenes de trabajo filtradas.');

        return Excel::download(new MechanicsFlatExcelExport($headings, $rows), $this->stampedExportFilename('ordenes-trabajo-mecanicas.xlsx'));
    }

    public function workOrdersGroupedTechnicianPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = FleetWorkOrderExportDatasets::groupedByTechnician($this->workOrdersFilteredQuery($request));
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'OT por tecnico', 'OT por tecnico', $headings, $rows);
        $this->logMechanicsExport($userAuditLogger, 'ot_por_tecnico_pdf', 'PDF OT agrupadas por tecnico.');

        return $this->streamPdf($pdf, 'ot-por-tecnico.pdf');
    }

    public function workOrdersGroupedTechnicianExcel(Request $request, UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = FleetWorkOrderExportDatasets::groupedByTechnician($this->workOrdersFilteredQuery($request));
        $this->logMechanicsExport($userAuditLogger, 'ot_por_tecnico_excel', 'Excel OT agrupadas por tecnico.');

        return Excel::download(new MechanicsFlatExcelExport($headings, $rows), $this->stampedExportFilename('ot-por-tecnico.xlsx'));
    }

    public function workOrdersGroupedProjectPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = FleetWorkOrderExportDatasets::groupedByProject($this->workOrdersFilteredQuery($request));
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'OT por obra', 'OT por obra', $headings, $rows);
        $this->logMechanicsExport($userAuditLogger, 'ot_por_obra_pdf', 'PDF OT agrupadas por obra.');

        return $this->streamPdf($pdf, 'ot-por-obra.pdf');
    }

    public function workOrdersGroupedProjectExcel(Request $request, UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = FleetWorkOrderExportDatasets::groupedByProject($this->workOrdersFilteredQuery($request));
        $this->logMechanicsExport($userAuditLogger, 'ot_por_obra_excel', 'Excel OT agrupadas por obra.');

        return Excel::download(new MechanicsFlatExcelExport($headings, $rows), $this->stampedExportFilename('ot-por-obra.xlsx'));
    }

    public function workOrdersGroupedEquipmentPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = FleetWorkOrderExportDatasets::groupedByEquipment($this->workOrdersFilteredQuery($request));
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'OT por equipo', 'OT por equipo', $headings, $rows);
        $this->logMechanicsExport($userAuditLogger, 'ot_por_equipo_pdf', 'PDF OT agrupadas por equipo.');

        return $this->streamPdf($pdf, 'ot-por-equipo.pdf');
    }

    public function workOrdersGroupedEquipmentExcel(Request $request, UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = FleetWorkOrderExportDatasets::groupedByEquipment($this->workOrdersFilteredQuery($request));
        $this->logMechanicsExport($userAuditLogger, 'ot_por_equipo_excel', 'Excel OT agrupadas por equipo.');

        return Excel::download(new MechanicsFlatExcelExport($headings, $rows), $this->stampedExportFilename('ot-por-equipo.xlsx'));
    }

    public function workOrdersOverduePdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = FleetWorkOrderExportDatasets::flatCostDetail((clone $this->overdueWorkOrdersFilteredQuery($request)));
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'OT vencidas', 'Ordenes vencidas (programacion)', $headings, $rows);
        $this->logMechanicsExport($userAuditLogger, 'ot_vencidas_pdf', 'PDF OT vencidas.');

        return $this->streamPdf($pdf, 'ot-vencidas.pdf');
    }

    public function workOrdersOverdueExcel(Request $request, UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = FleetWorkOrderExportDatasets::flatCostDetail((clone $this->overdueWorkOrdersFilteredQuery($request)));
        $this->logMechanicsExport($userAuditLogger, 'ot_vencidas_excel', 'Excel OT vencidas.');

        return Excel::download(new MechanicsFlatExcelExport($headings, $rows), $this->stampedExportFilename('ot-vencidas.xlsx'));
    }

    public function workOrdersTypesPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = FleetWorkOrderExportDatasets::preventiveVsCorrective(clone $this->workOrdersFilteredQuery($request));
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'OT preventivo vs correctivo', 'Conteo OT por tipo', $headings, $rows);
        $this->logMechanicsExport($userAuditLogger, 'ot_tipos_pdf', 'PDF conteo OT por tipo.');

        return $this->streamPdf($pdf, 'ot-preventivo-correctivo.pdf');
    }

    public function workOrdersTypesExcel(Request $request, UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = FleetWorkOrderExportDatasets::preventiveVsCorrective(clone $this->workOrdersFilteredQuery($request));
        $this->logMechanicsExport($userAuditLogger, 'ot_tipos_excel', 'Excel conteo OT por tipo.');

        return Excel::download(new MechanicsFlatExcelExport($headings, $rows), $this->stampedExportFilename('ot-preventivo-correctivo.xlsx'));
    }

    public function workOrdersCostsPdf(Request $request, UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse
    {
        $this->authorizeMechanicsExport();
        $query = clone $this->workOrdersFilteredQuery($request);
        $total = (clone $query)->sum('total_cost');
        [$headings, $rows] = FleetWorkOrderExportDatasets::flatCostDetail((clone $query));
        $summary = [
            'Registros: '.$rows->count(),
            'Costo total MO + repuestos (filtros): S/ '.number_format((float) $total, 2, '.', ''),
        ];
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Costos por OT', 'Costos por OT', $headings, $rows, $summary);
        $this->logMechanicsExport($userAuditLogger, 'ot_costos_pdf', 'PDF costos OT filtradas.');

        return $this->streamPdf($pdf, 'ot-costos.pdf');
    }

    public function workOrdersCostsExcel(Request $request, UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = FleetWorkOrderExportDatasets::flatCostDetail((clone $this->workOrdersFilteredQuery($request)));
        $this->logMechanicsExport($userAuditLogger, 'ot_costos_excel', 'Excel costos OT filtradas.');

        return Excel::download(new MechanicsFlatExcelExport($headings, $rows), $this->stampedExportFilename('ot-costos.xlsx'));
    }

    public function maintenanceCostsPdf(UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport, MechanicalCostAnalytics $mechanicalCostAnalytics): StreamedResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = $this->maintenanceCostsDataset($mechanicalCostAnalytics);
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Costos de mantenimiento', 'Costos de mantenimiento', $headings, $rows);
        $this->logMechanicsExport($userAuditLogger, 'costos_pdf', 'PDF costos mantenimiento.');

        return $this->streamPdf($pdf, 'costos-mantenimiento.pdf');
    }

    public function maintenanceCostsExcel(UserAuditLogger $userAuditLogger, MechanicalCostAnalytics $mechanicalCostAnalytics): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = $this->maintenanceCostsDataset($mechanicalCostAnalytics);
        $this->logMechanicsExport($userAuditLogger, 'costos_excel', 'Excel costos mantenimiento.');

        return Excel::download(new MechanicsFlatExcelExport($headings, $rows), $this->stampedExportFilename('costos-mantenimiento.xlsx'));
    }

    public function consumedSparesPdf(UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = $this->consumedSparesDataset();
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Repuestos consumidos', 'Repuestos consumidos', $headings, $rows);
        $this->logMechanicsExport($userAuditLogger, 'repuestos_consumidos_pdf', 'PDF repuestos consumidos.');

        return $this->streamPdf($pdf, 'repuestos-consumidos.pdf');
    }

    public function consumedSparesExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = $this->consumedSparesDataset();
        $this->logMechanicsExport($userAuditLogger, 'repuestos_consumidos_excel', 'Excel repuestos consumidos.');

        return Excel::download(new MechanicsFlatExcelExport($headings, $rows), $this->stampedExportFilename('repuestos-consumidos.xlsx'));
    }

    public function equipmentByProjectPdf(UserAuditLogger $userAuditLogger, GenericMechanicsPdfReport $genericMechanicsPdfReport): StreamedResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = $this->equipmentByProjectDataset();
        $pdf = $genericMechanicsPdfReport->build(auth()->user(), 'Equipos por obra', 'Equipos por obra', $headings, $rows);
        $this->logMechanicsExport($userAuditLogger, 'equipos_por_obra_pdf', 'PDF equipos por obra.');

        return $this->streamPdf($pdf, 'equipos-por-obra.pdf');
    }

    public function equipmentByProjectExcel(UserAuditLogger $userAuditLogger): BinaryFileResponse
    {
        $this->authorizeMechanicsExport();
        [$headings, $rows] = $this->equipmentByProjectDataset();
        $this->logMechanicsExport($userAuditLogger, 'equipos_por_obra_excel', 'Excel equipos por obra.');

        return Excel::download(new MechanicsFlatExcelExport($headings, $rows), $this->stampedExportFilename('equipos-por-obra.xlsx'));
    }

    protected function authorizeMechanicsExport(): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless(auth()->user()->can('mecanica.exportar'), 403);
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
     * @return array{0: list<string>, 1: list<string>, 2: Collection<int, array<string|int|null>>}
     */
    protected function machineryStatusDataset(): array
    {
        $counts = FleetEquipment::query()
            ->selectRaw('operational_status, COUNT(*) as c')
            ->groupBy('operational_status')
            ->get();

        $summaryLines = $counts->map(fn ($row): string => 'Estado '.(string) $row->operational_status.': '.(string) $row->c.' equipos.')->all();

        $equipments = FleetEquipment::query()
            ->with(['workProject', 'responsibleUser'])
            ->orderBy('operational_status')
            ->orderBy('internal_code')
            ->get();

        $headings = [
            'Estado operativo', 'Codigo', 'Nombre', 'Tipo', 'Obra', 'Responsable', 'Km', 'Horometro',
        ];

        $rows = $equipments->map(fn (FleetEquipment $equipment): array => [
            $equipment->operational_status,
            $equipment->internal_code,
            $equipment->name,
            $equipment->equipment_type,
            $equipment->workProject?->code,
            $equipment->responsibleUser?->name,
            $equipment->odometer_km,
            $equipment->hour_meter,
        ])->values();

        return [$summaryLines, $headings, $rows];
    }

    /**
     * @return array{0: list<string>, 1: Collection<int, array<string|int|null>>}
     */
    protected function inspectionsDataset(): array
    {
        $rows = FleetTechnicalInspection::query()
            ->with(['equipment', 'responsibleUser'])
            ->orderByDesc('due_at')
            ->get()
            ->map(fn (FleetTechnicalInspection $inspection): array => [
                $inspection->equipment?->internal_code,
                $inspection->equipment?->name,
                $inspection->reviewed_at?->format('Y-m-d'),
                $inspection->due_at?->format('Y-m-d'),
                $inspection->result,
                $inspection->inspection_center ?? '',
                $inspection->status,
                $inspection->responsibleUser?->name ?? '',
            ])
            ->values();

        $headings = ['Eq. codigo', 'Eq. nombre', 'F. revision', 'Vencimiento', 'Resultado', 'Centro', 'Estado', 'Responsable'];

        return [$headings, $rows];
    }

    /**
     * @return array{0: list<string>, 1: Collection<int, array<string|int|float|null>>}
     */
    protected function preventiveDataset(): array
    {
        $rows = FleetPreventiveMaintenance::query()
            ->with(['equipment', 'responsibleUser'])
            ->orderByDesc('scheduled_date')
            ->get()
            ->map(fn (FleetPreventiveMaintenance $row): array => [
                $row->equipment?->internal_code,
                $row->maintenance_type,
                $row->scheduled_date?->format('Y-m-d'),
                $row->status,
                $row->priority,
                $row->scheduled_odometer,
                $row->scheduled_hour_meter,
                $row->cost,
                $row->responsibleUser?->name ?? '',
            ])
            ->values();

        $headings = ['Eq. codigo', 'Tipo', 'F. programada', 'Estado', 'Prioridad', 'Km prog.', 'Hrs prog.', 'Costo', 'Responsable'];

        return [$headings, $rows];
    }

    /**
     * @return array{0: list<string>, 1: Collection<int, array<string|int|float|null>>}
     */
    protected function correctiveDataset(): array
    {
        $rows = FleetCorrectiveMaintenance::query()
            ->with(['equipment', 'responsibleUser'])
            ->orderByDesc('failure_at')
            ->get()
            ->map(fn (FleetCorrectiveMaintenance $row): array => [
                $row->equipment?->internal_code,
                $row->failure_at?->format('Y-m-d H:i'),
                $row->status,
                $row->supplier_workshop ?? '',
                $row->estimated_cost,
                $row->real_cost,
                mb_substr(strip_tags($row->failure_description), 0, 120),
                $row->responsibleUser?->name ?? '',
            ])
            ->values();

        $headings = ['Eq. codigo', 'Fecha falla', 'Estado', 'Taller', 'Costo est.', 'Costo real', 'Falla (res.)', 'Responsable'];

        return [$headings, $rows];
    }

    /**
     * @return Builder<FleetWorkOrder>
     */
    protected function workOrdersFilteredQuery(Request $request): Builder
    {
        $filters = FleetWorkOrderBoardQuery::filtersFromRequest($request);
        $query = FleetWorkOrder::query()->with(['equipment', 'workProject', 'responsibleUser']);
        FleetWorkOrderBoardQuery::apply($query, $filters);
        $this->applyWorkOrderExportSort($request, $query);

        return $query;
    }

    /**
     * @return Builder<FleetWorkOrder>
     */
    protected function overdueWorkOrdersFilteredQuery(Request $request): Builder
    {
        $filters = FleetWorkOrderBoardQuery::filtersFromRequest($request);
        $filters['overdue_only'] = true;

        $query = FleetWorkOrder::query()->with(['equipment', 'workProject', 'responsibleUser']);
        FleetWorkOrderBoardQuery::apply($query, $filters);
        $this->applyWorkOrderExportSort($request, $query);

        return $query;
    }

    protected function applyWorkOrderExportSort(Request $request, Builder $query): void
    {
        $sort = $request->input('sort', 'issued_at');
        $dir = strtolower((string) $request->input('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        /** @var list<string> $allowed */
        $allowed = ['code', 'issued_at', 'scheduled_date', 'closed_at', 'total_cost', 'status', 'priority', 'type'];

        if (is_string($sort) && in_array($sort, $allowed, true)) {
            $query->orderBy($sort, $dir);
        } else {
            $query->orderByDesc('issued_at');
        }
    }

    /**
     * @return array{0: list<string>, 1: Collection<int, array<string|float>>}
     */
    protected function maintenanceCostsDataset(MechanicalCostAnalytics $mechanicalCostAnalytics): array
    {
        $data = $mechanicalCostAnalytics->build(null);
        /** @var Collection<int, array<string|float|string>> $rows */
        $rows = collect();

        $rows->push([
            'Resumen', 'Preventivo total (OT + registros huérfanos)', number_format((float) $data['preventivo_total'], 2, '.', ''),
        ]);

        $rows->push([
            'Resumen', 'Correctivo total (OT + registros huérfanos)', number_format((float) $data['correctivo_total'], 2, '.', ''),
        ]);

        foreach ($data['by_equipment'] as $bucket) {
            $rows->push(['Por equipo', (string) $bucket['label'], number_format((float) $bucket['total'], 2, '.', '')]);
        }

        foreach ($data['by_project'] as $bucket) {
            $rows->push(['Por obra', (string) $bucket['label'], number_format((float) $bucket['total'], 2, '.', '')]);
        }

        foreach ($data['by_work_order_type'] as $bucket) {
            $rows->push([
                'OT por tipo',
                (string) $bucket['type'],
                number_format((float) $bucket['total'], 2, '.', ''),
            ]);
        }

        foreach ($data['monthly'] as $month) {
            $rows->push([
                'Mensual',
                (string) $month['label'].' total',
                number_format((float) $month['total'], 2, '.', ''),
            ]);
            $rows->push([
                'Mensual',
                (string) $month['label'].' preventivo',
                number_format((float) $month['preventivo'], 2, '.', ''),
            ]);
            $rows->push([
                'Mensual',
                (string) $month['label'].' correctivo',
                number_format((float) $month['correctivo'], 2, '.', ''),
            ]);
        }

        foreach ($mechanicalCostAnalytics->bySupplier() as $row) {
            $rows->push([
                'Taller proveedor',
                $row['supplier'],
                number_format((float) $row['total'], 2, '.', ''),
            ]);
        }

        $rows->push([
            'Inventario', 'Valor salidas repuestos (kardex salidas)', number_format((float) $mechanicalCostAnalytics->consumedSparesValue(null), 2, '.', ''),
        ]);

        $headings = ['Categoria', 'Concepto', 'Importe S/'];

        return [$headings, $rows->values()];
    }

    /**
     * @return array{0: list<string>, 1: Collection<int, array<string|int|null>>}
     */
    protected function consumedSparesDataset(): array
    {
        $rows = FleetSparePartMovement::query()
            ->with(['sparePart', 'workOrder'])
            ->where('direction', FleetSparePartMovementDirection::Outbound->value())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (FleetSparePartMovement $row): array => [
                $row->created_at?->format('Y-m-d H:i'),
                $row->movement_code,
                $row->sparePart?->code,
                $row->sparePart?->name,
                $row->quantity,
                $row->unit_cost,
                $row->total_amount,
                $row->workOrder?->code ?? '',
                $row->reference ?? '',
            ])
            ->values();

        $headings = ['Fecha', 'Mov.', 'Rep. codigo', 'Nombre', 'Cant.', 'CU', 'Importe', 'OT', 'Ref.'];

        return [$headings, $rows];
    }

    /**
     * @return array{0: list<string>, 1: Collection<int, array<int|string|null>>}
     */
    protected function equipmentByProjectDataset(): array
    {
        /** @var Collection<int, FleetEquipment> $equipment */
        $equipment = FleetEquipment::query()->with(['workProject'])->orderBy('internal_code')->get();

        /** @var Collection<string, Collection<int, FleetEquipment>> $grouped */
        $grouped = $equipment->groupBy(fn (FleetEquipment $row): string => (string) ($row->work_project_id ?? ''));

        $rows = $grouped->map(function (Collection $items, ?string $projectId): array {
            /** @var FleetEquipment|null $first */
            $first = $items->first();

            $codes = $items->map(fn (FleetEquipment $equipment): string => $equipment->internal_code)->join(', ');
            $count = $items->count();

            if ($first?->workProject) {
                return [
                    $first->workProject->code ?? '',
                    $first->workProject->name ?? '',
                    $count,
                    $codes,
                ];
            }

            return ['(sin obra)', '', $count, $codes];
        })->values();

        $headings = ['Obra codigo', 'Obra nombre', 'Cantidad equipos', 'Equipos (codigos)'];

        return [$headings, $rows];
    }
}
