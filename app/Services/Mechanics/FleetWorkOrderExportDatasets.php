<?php

namespace App\Services\Mechanics;

use App\Enums\FleetWorkOrderStatus;
use App\Enums\FleetWorkOrderType;
use App\Models\FleetWorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FleetWorkOrderExportDatasets
{
    /**
     * @param  Builder<FleetWorkOrder>  $query
     * @return array{0: list<string>, 1: Collection<int, array<int|string|float|null>>}
     */
    public static function flatCostDetail(Builder $query): array
    {
        $rows = $query
            ->get()
            ->map(fn (FleetWorkOrder $row): array => [
                $row->code,
                $row->equipment?->internal_code,
                $row->equipment?->name,
                $row->workProject?->code,
                $row->workProject?->name,
                $row->responsibleUser?->name,
                self::typeLabel($row->type),
                self::statusLabel($row->status),
                $row->priority,
                $row->issued_at?->format('Y-m-d'),
                $row->scheduled_date?->format('Y-m-d'),
                $row->closed_at?->format('Y-m-d H:i'),
                $row->scheduleRiskLabel(),
                (float) $row->labor_cost,
                (float) $row->spare_parts_cost,
                (float) $row->total_cost,
            ])
            ->values();

        $headings = [
            'Codigo OT', 'Eq. codigo', 'Eq. nombre', 'Obra cod.', 'Obra nombre', 'Tecnico', 'Tipo', 'Estado', 'Prioridad',
            'Emision', 'Programada', 'Cierre', 'Alerta agenda', 'MO S/', 'Repuestos S/', 'Total S/',
        ];

        return [$headings, $rows];
    }

    /**
     * @param  Builder<FleetWorkOrder>  $query
     * @return array{0: list<string>, 1: Collection<int, array<int|string|float>>}
     */
    public static function groupedByTechnician(Builder $query): array
    {
        $collection = $query->get();

        /** @var Collection<string, Collection<int, FleetWorkOrder>> $grouped */
        $grouped = $collection->groupBy(fn (FleetWorkOrder $wo): string => (string) ($wo->responsible_user_id ?? ''));

        $rows = $grouped->map(function (Collection $orders, string $userId): array {
            /** @var FleetWorkOrder|null $first */
            $first = $orders->first();

            $technician = $userId === '' ? '(sin asignar)' : (string) ($first?->responsibleUser?->name ?? 'Usuario #'.$userId);
            $open = $orders->whereIn('status', FleetWorkOrderStatus::openStatuses())->count();
            $overdue = $orders->filter(fn (FleetWorkOrder $wo): bool => self::isOpenOverdue($wo))->count();
            $totalCost = (float) $orders->sum(fn (FleetWorkOrder $wo): float => (float) $wo->total_cost);

            return [$technician, $orders->count(), $open, $overdue, round($totalCost, 2)];
        })->values();

        $headings = ['Tecnico', 'Total OT', 'Abiertas', 'Vencidas prog.', 'Costo acum. S/'];

        return [$headings, $rows];
    }

    /**
     * @param  Builder<FleetWorkOrder>  $query
     * @return array{0: list<string>, 1: Collection<int, array<int|string|float>>}
     */
    public static function groupedByProject(Builder $query): array
    {
        $collection = $query->get();

        /** @var Collection<string, Collection<int, FleetWorkOrder>> $grouped */
        $grouped = $collection->groupBy(fn (FleetWorkOrder $wo): string => (string) ($wo->work_project_id ?? ''));

        $rows = $grouped->map(function (Collection $orders, string $projectId): array {
            /** @var FleetWorkOrder|null $first */
            $first = $orders->first();

            $label = $projectId === '' ? '(sin obra)' : (string) (($first?->workProject?->code ?? '').' — '.($first?->workProject?->name ?? ''));
            $open = $orders->whereIn('status', FleetWorkOrderStatus::openStatuses())->count();
            $overdue = $orders->filter(fn (FleetWorkOrder $wo): bool => self::isOpenOverdue($wo))->count();
            $totalCost = (float) $orders->sum(fn (FleetWorkOrder $wo): float => (float) $wo->total_cost);

            return [$label, $orders->count(), $open, $overdue, round($totalCost, 2)];
        })->values();

        $headings = ['Obra', 'Total OT', 'Abiertas', 'Vencidas prog.', 'Costo acum. S/'];

        return [$headings, $rows];
    }

    /**
     * @param  Builder<FleetWorkOrder>  $query
     * @return array{0: list<string>, 1: Collection<int, array<int|string|float>>}
     */
    public static function groupedByEquipment(Builder $query): array
    {
        $collection = $query->get();

        /** @var Collection<int|string, Collection<int, FleetWorkOrder>> $grouped */
        $grouped = $collection->groupBy('fleet_equipment_id');

        $rows = $grouped->map(function (Collection $orders, int|string $equipmentId): array {
            /** @var FleetWorkOrder|null $first */
            $first = $orders->first();

            $eq = $first?->equipment;
            $label = $eq ? $eq->internal_code.' — '.$eq->name : 'Equipo #'.$equipmentId;
            $open = $orders->whereIn('status', FleetWorkOrderStatus::openStatuses())->count();
            $overdue = $orders->filter(fn (FleetWorkOrder $wo): bool => self::isOpenOverdue($wo))->count();
            $totalCost = (float) $orders->sum(fn (FleetWorkOrder $wo): float => (float) $wo->total_cost);

            return [$label, $orders->count(), $open, $overdue, round($totalCost, 2)];
        })->values();

        $headings = ['Equipo', 'Total OT', 'Abiertas', 'Vencidas prog.', 'Costo acum. S/'];

        return [$headings, $rows];
    }

    /**
     * Preventivo vs correctivo (recuento dentro del conjunto filtrado).
     *
     * @param  Builder<FleetWorkOrder>  $query
     * @return array{0: list<string>, 1: Collection<int, array<string|int>>}
     */
    public static function preventiveVsCorrective(Builder $query): array
    {
        $rows = collect(FleetWorkOrderType::cases())->map(function ($type) use ($query): array {
            $count = (clone $query)->where('type', $type->value())->count();

            return [$type->label(), $count];
        })->values();

        return [['Tipo de OT', 'Cantidad'], $rows];
    }

    protected static function typeLabel(?string $value): string
    {
        foreach (FleetWorkOrderType::cases() as $case) {
            if ($case->value() === $value) {
                return $case->label();
            }
        }

        return (string) $value;
    }

    protected static function statusLabel(?string $value): string
    {
        foreach (FleetWorkOrderStatus::cases() as $case) {
            if ($case->value() === $value) {
                return $case->label();
            }
        }

        return (string) $value;
    }

    protected static function isOpenOverdue(FleetWorkOrder $wo): bool
    {
        if (! in_array((string) $wo->status, FleetWorkOrderStatus::openStatuses(), true)) {
            return false;
        }

        return $wo->scheduled_date !== null && $wo->scheduled_date->toDateString() < today()->toDateString();
    }
}
