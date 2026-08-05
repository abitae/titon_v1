<?php

namespace App\Services\Mechanics;

use App\Enums\FleetSparePartMovementDirection;
use App\Models\FleetCorrectiveMaintenance;
use App\Models\FleetEquipment;
use App\Models\FleetPreventiveMaintenance;
use App\Models\FleetSparePartMovement;
use App\Models\FleetTechnicalInspection;
use App\Models\FleetWorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class MechanicsReportDatasets
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|float|null>>, summary: list<string>}
     */
    public function for(string $reportKey, array $filters = []): array
    {
        return match ($reportKey) {
            'equipments' => $this->equipments(),
            'machinery-status' => $this->machineryStatus(),
            'equipment-by-project' => $this->equipmentByProject(),
            'inspections' => $this->inspections(),
            'preventive' => $this->preventive(),
            'corrective' => $this->corrective(),
            'maintenance-costs' => $this->maintenanceCosts(),
            'work-orders' => $this->workOrdersFlat($filters),
            'work-orders-by-technician' => $this->workOrdersGroupedByTechnician($filters),
            'work-orders-by-project' => $this->workOrdersGroupedByProject($filters),
            'work-orders-by-equipment' => $this->workOrdersGroupedByEquipment($filters),
            'work-orders-overdue' => $this->workOrdersOverdueFlat($filters),
            'work-orders-types' => $this->workOrdersTypes($filters),
            'work-orders-costs' => $this->workOrdersCosts($filters),
            'consumed-spares' => $this->consumedSpares(),
            default => throw new InvalidArgumentException("Reporte de mecanica desconocido: {$reportKey}"),
        };
    }

    /**
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|null>>, summary: list<string>}
     */
    public function equipments(): array
    {
        $rows = FleetEquipment::query()
            ->with(['workProject', 'responsibleUser'])
            ->orderBy('internal_code')
            ->get()
            ->map(fn (FleetEquipment $equipment): array => [
                $equipment->internal_code,
                $equipment->equipment_type,
                $equipment->name,
                $equipment->brand,
                $equipment->model,
                $equipment->serial_number,
                $equipment->plate,
                $equipment->city,
                trim(($equipment->workProject?->code ?? '').($equipment->workProject?->name ? ' — '.$equipment->workProject->name : '')) ?: '',
                $equipment->responsibleUser?->name ?? '',
                $equipment->operational_status,
                $equipment->odometer_km,
                $equipment->hour_meter,
            ])
            ->values();

        return [
            'headings' => [
                'Codigo', 'Tipo', 'Nombre', 'Marca', 'Modelo', 'Serie', 'Placa', 'Ciudad', 'Obra', 'Responsable', 'Estado', 'Km', 'Horometro',
            ],
            'rows' => $rows,
            'summary' => ['Total equipos: '.$rows->count()],
        ];
    }

    /**
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|null>>, summary: list<string>}
     */
    public function machineryStatus(): array
    {
        $counts = FleetEquipment::query()
            ->selectRaw('operational_status, COUNT(*) as c')
            ->groupBy('operational_status')
            ->get();

        $summary = $counts
            ->map(fn ($row): string => 'Estado '.(string) $row->operational_status.': '.(string) $row->c.' equipos.')
            ->values()
            ->all();

        $equipments = FleetEquipment::query()
            ->with(['workProject', 'responsibleUser'])
            ->orderBy('operational_status')
            ->orderBy('internal_code')
            ->get();

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

        return [
            'headings' => [
                'Estado operativo', 'Codigo', 'Nombre', 'Tipo', 'Obra', 'Responsable', 'Km', 'Horometro',
            ],
            'rows' => $rows,
            'summary' => $summary,
        ];
    }

    /**
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|null>>, summary: list<string>}
     */
    public function equipmentByProject(): array
    {
        /** @var Collection<int, FleetEquipment> $equipment */
        $equipment = FleetEquipment::query()->with(['workProject'])->orderBy('internal_code')->get();

        /** @var Collection<string, Collection<int, FleetEquipment>> $grouped */
        $grouped = $equipment->groupBy(fn (FleetEquipment $row): string => (string) ($row->work_project_id ?? ''));

        $rows = $grouped->map(function (Collection $items, ?string $projectId): array {
            /** @var FleetEquipment|null $first */
            $first = $items->first();

            $codes = $items->map(fn (FleetEquipment $equipment): string => $equipment->internal_code)->join(', ');

            if ($first?->workProject) {
                return [
                    $first->workProject->code ?? '',
                    $first->workProject->name ?? '',
                    $items->count(),
                    $codes,
                ];
            }

            return ['(sin obra)', '', $items->count(), $codes];
        })->values();

        return [
            'headings' => ['Obra codigo', 'Obra nombre', 'Cantidad equipos', 'Equipos (codigos)'],
            'rows' => $rows,
            'summary' => ['Grupos por obra: '.$rows->count()],
        ];
    }

    /**
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|null>>, summary: list<string>}
     */
    public function inspections(): array
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

        return [
            'headings' => ['Eq. codigo', 'Eq. nombre', 'F. revision', 'Vencimiento', 'Resultado', 'Centro', 'Estado', 'Responsable'],
            'rows' => $rows,
            'summary' => ['Revisiones registradas: '.$rows->count()],
        ];
    }

    /**
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|float|null>>, summary: list<string>}
     */
    public function preventive(): array
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

        return [
            'headings' => ['Eq. codigo', 'Tipo', 'F. programada', 'Estado', 'Prioridad', 'Km prog.', 'Hrs prog.', 'Costo', 'Responsable'],
            'rows' => $rows,
            'summary' => ['Mantenimientos preventivos: '.$rows->count()],
        ];
    }

    /**
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|float|null>>, summary: list<string>}
     */
    public function corrective(): array
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

        return [
            'headings' => ['Eq. codigo', 'Fecha falla', 'Estado', 'Taller', 'Costo est.', 'Costo real', 'Falla (res.)', 'Responsable'],
            'rows' => $rows,
            'summary' => ['Mantenimientos correctivos: '.$rows->count()],
        ];
    }

    /**
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|float|null>>, summary: list<string>}
     */
    public function maintenanceCosts(): array
    {
        $mechanicalCostAnalytics = app(MechanicalCostAnalytics::class);
        $data = $mechanicalCostAnalytics->build(null);
        /** @var Collection<int, array<int|string|float|null>> $rows */
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

        return [
            'headings' => ['Categoria', 'Concepto', 'Importe S/'],
            'rows' => $rows->values(),
            'summary' => ['Analisis consolidado de costos de mantenimiento'],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|float|null>>, summary: list<string>}
     */
    public function workOrdersFlat(array $filters = []): array
    {
        [$headings, $rows] = FleetWorkOrderExportDatasets::flatCostDetail(
            (clone $this->workOrdersQuery($filters)),
        );

        return [
            'headings' => $headings,
            'rows' => $rows,
            'summary' => ['Ordenes de trabajo: '.$rows->count()],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|float|null>>, summary: list<string>}
     */
    public function workOrdersGroupedByTechnician(array $filters = []): array
    {
        [$headings, $rows] = FleetWorkOrderExportDatasets::groupedByTechnician(
            $this->workOrdersQuery($filters),
        );

        return [
            'headings' => $headings,
            'rows' => $rows,
            'summary' => ['Tecnicos con OT: '.$rows->count()],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|float|null>>, summary: list<string>}
     */
    public function workOrdersGroupedByProject(array $filters = []): array
    {
        [$headings, $rows] = FleetWorkOrderExportDatasets::groupedByProject(
            $this->workOrdersQuery($filters),
        );

        return [
            'headings' => $headings,
            'rows' => $rows,
            'summary' => ['Obras con OT: '.$rows->count()],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|float|null>>, summary: list<string>}
     */
    public function workOrdersGroupedByEquipment(array $filters = []): array
    {
        [$headings, $rows] = FleetWorkOrderExportDatasets::groupedByEquipment(
            $this->workOrdersQuery($filters),
        );

        return [
            'headings' => $headings,
            'rows' => $rows,
            'summary' => ['Equipos con OT: '.$rows->count()],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|float|null>>, summary: list<string>}
     */
    public function workOrdersOverdueFlat(array $filters = []): array
    {
        [$headings, $rows] = FleetWorkOrderExportDatasets::flatCostDetail(
            (clone $this->overdueWorkOrdersQuery($filters)),
        );

        return [
            'headings' => $headings,
            'rows' => $rows,
            'summary' => ['Ordenes vencidas: '.$rows->count()],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|float|null>>, summary: list<string>}
     */
    public function workOrdersTypes(array $filters = []): array
    {
        [$headings, $rows] = FleetWorkOrderExportDatasets::preventiveVsCorrective(
            clone $this->workOrdersQuery($filters),
        );

        return [
            'headings' => $headings,
            'rows' => $rows,
            'summary' => ['Tipos de OT: '.$rows->count()],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|float|null>>, summary: list<string>}
     */
    public function workOrdersCosts(array $filters = []): array
    {
        $query = clone $this->workOrdersQuery($filters);
        $total = (clone $query)->sum('total_cost');
        [$headings, $rows] = FleetWorkOrderExportDatasets::flatCostDetail((clone $query));

        return [
            'headings' => $headings,
            'rows' => $rows,
            'summary' => [
                'Registros: '.$rows->count(),
                'Costo total MO + repuestos: S/ '.number_format((float) $total, 2, '.', ''),
            ],
        ];
    }

    /**
     * @return array{headings: list<string>, rows: Collection<int, array<int|string|float|null>>, summary: list<string>}
     */
    public function consumedSpares(): array
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

        return [
            'headings' => ['Fecha', 'Mov.', 'Rep. codigo', 'Nombre', 'Cant.', 'CU', 'Importe', 'OT', 'Ref.'],
            'rows' => $rows,
            'summary' => ['Salidas de repuestos: '.$rows->count()],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<FleetWorkOrder>
     */
    public function workOrdersQuery(array $filters): Builder
    {
        $query = FleetWorkOrder::query()->with(['equipment', 'workProject', 'responsibleUser']);
        FleetWorkOrderBoardQuery::apply($query, $filters);
        $this->applyWorkOrderSort($filters, $query);

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<FleetWorkOrder>
     */
    public function overdueWorkOrdersQuery(array $filters): Builder
    {
        $filters['overdue_only'] = true;

        return $this->workOrdersQuery($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  Builder<FleetWorkOrder>  $query
     */
    protected function applyWorkOrderSort(array $filters, Builder $query): void
    {
        $sort = $filters['sort'] ?? 'issued_at';
        $dir = strtolower((string) ($filters['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        /** @var list<string> $allowed */
        $allowed = ['code', 'issued_at', 'scheduled_date', 'closed_at', 'total_cost', 'status', 'priority', 'type'];

        if (is_string($sort) && in_array($sort, $allowed, true)) {
            $query->orderBy($sort, $dir);
        } else {
            $query->orderByDesc('issued_at');
        }
    }
}
