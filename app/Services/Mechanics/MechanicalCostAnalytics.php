<?php

namespace App\Services\Mechanics;

use App\Enums\FleetCorrectiveMaintenanceStatus;
use App\Enums\FleetPreventiveMaintenanceStatus;
use App\Enums\FleetSparePartMovementDirection;
use App\Enums\FleetTechnicalInspectionStatus;
use App\Enums\FleetWorkOrderStatus;
use App\Enums\FleetWorkOrderType;
use App\Models\FleetCorrectiveMaintenance;
use App\Models\FleetPreventiveMaintenance;
use App\Models\FleetSparePartMovement;
use App\Models\FleetTechnicalInspection;
use App\Models\FleetWorkOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MechanicalCostAnalytics
{
    /**
     * @return array<string, mixed>
     */
    public function build(?int $companyId = null): array
    {
        $closedWorkOrders = FleetWorkOrder::query()
            ->when($companyId !== null, fn ($q) => $q->where('fleet_work_orders.company_id', $companyId))
            ->with(['equipment:id,internal_code,name', 'workProject:id,code,name'])
            ->whereIn('status', FleetWorkOrderStatus::countedCostStatuses())
            ->get();

        $equipmentLabel = function (FleetWorkOrder $row): string {
            return trim(($row->equipment?->internal_code ?? '').' · '.($row->equipment?->name ?? '')) ?: 'Sin equipo';
        };

        $byEquipment = $closedWorkOrders
            ->filter(fn (FleetWorkOrder $row): bool => $row->fleet_equipment_id !== null)
            ->groupBy('fleet_equipment_id')
            ->map(fn (Collection $items) => [
                'label' => $equipmentLabel($items->first()),
                'total' => (float) $items->sum(fn (FleetWorkOrder $w) => (float) $w->total_cost),
            ])
            ->values()
            ->all();

        $byProject = $closedWorkOrders
            ->filter(fn (FleetWorkOrder $row): bool => $row->work_project_id !== null)
            ->groupBy('work_project_id')
            ->map(function (Collection $items) {
                $first = $items->first();

                return [
                    'label' => $first?->workProject
                        ? $first->workProject->code.' · '.$first->workProject->name
                        : 'Obra',
                    'total' => (float) $items->sum(fn (FleetWorkOrder $w) => (float) $w->total_cost),
                ];
            })
            ->values()
            ->all();

        $byWoType = $closedWorkOrders
            ->groupBy('type')
            ->map(fn (Collection $items, string $type) => [
                'type' => $type,
                'total' => (float) $items->sum(fn (FleetWorkOrder $w) => (float) $w->total_cost),
            ])
            ->values()
            ->all();

        $now = Carbon::now();

        $monthlyTotals = [];

        foreach (range(11, 0) as $i) {
            $monthStart = $now->copy()->subMonthsNoOverflow((int) $i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $label = $monthStart->locale('es')->translatedFormat('M Y');

            $woSum = (float) FleetWorkOrder::query()
                ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
                ->whereIn('status', FleetWorkOrderStatus::countedCostStatuses())
                ->whereNotNull('closed_at')
                ->whereBetween('closed_at', [$monthStart, $monthEnd])
                ->sum('total_cost');

            $preventiveOrphan = (float) FleetPreventiveMaintenance::query()
                ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
                ->where('status', FleetPreventiveMaintenanceStatus::Completed->value())
                ->whereDoesntHave('workOrders')
                ->whereBetween('updated_at', [$monthStart, $monthEnd])
                ->sum('cost');

            $correctiveOrphan = (float) FleetCorrectiveMaintenance::query()
                ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
                ->whereIn('status', [FleetCorrectiveMaintenanceStatus::Repaired->value(), FleetCorrectiveMaintenanceStatus::Closed->value()])
                ->whereDoesntHave('workOrders')
                ->whereBetween('updated_at', [$monthStart, $monthEnd])
                ->sum('real_cost');

            $woPreventive = (float) FleetWorkOrder::query()
                ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
                ->whereIn('status', FleetWorkOrderStatus::countedCostStatuses())
                ->where('type', FleetWorkOrderType::Preventive->value())
                ->whereNotNull('closed_at')
                ->whereBetween('closed_at', [$monthStart, $monthEnd])
                ->sum('total_cost');

            $woCorrective = (float) FleetWorkOrder::query()
                ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
                ->whereIn('status', FleetWorkOrderStatus::countedCostStatuses())
                ->where('type', FleetWorkOrderType::Corrective->value())
                ->whereNotNull('closed_at')
                ->whereBetween('closed_at', [$monthStart, $monthEnd])
                ->sum('total_cost');

            $monthlyTotals[] = [
                'label' => $label,
                'total' => $woSum + $preventiveOrphan + $correctiveOrphan,
                'preventivo' => $preventiveOrphan + $woPreventive,
                'correctivo' => $correctiveOrphan + $woCorrective,
            ];
        }

        $preventiveTotal = (float) FleetPreventiveMaintenance::query()
            ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
            ->where('status', FleetPreventiveMaintenanceStatus::Completed->value())
            ->whereDoesntHave('workOrders')
            ->sum('cost')
            + (float) FleetWorkOrder::query()
                ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
                ->whereIn('status', FleetWorkOrderStatus::countedCostStatuses())
                ->where('type', FleetWorkOrderType::Preventive->value())
                ->sum('total_cost');

        $correctiveTotal = (float) FleetCorrectiveMaintenance::query()
            ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
            ->whereIn('status', [FleetCorrectiveMaintenanceStatus::Repaired->value(), FleetCorrectiveMaintenanceStatus::Closed->value()])
            ->whereDoesntHave('workOrders')
            ->sum('real_cost')
            + (float) FleetWorkOrder::query()
                ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
                ->whereIn('status', FleetWorkOrderStatus::countedCostStatuses())
                ->where('type', FleetWorkOrderType::Corrective->value())
                ->sum('total_cost');

        return [
            'by_equipment' => $byEquipment,
            'by_project' => $byProject,
            'by_work_order_type' => $byWoType,
            'monthly' => $monthlyTotals,
            'preventivo_total' => $preventiveTotal,
            'correctivo_total' => $correctiveTotal,
        ];
    }

    /**
     * @return array<int, array{supplier:string, total:float}>
     */
    public function bySupplier(?int $companyId = null): array
    {
        return FleetCorrectiveMaintenance::query()
            ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
            ->whereNotNull('supplier_workshop')
            ->where('supplier_workshop', '!=', '')
            ->whereIn('status', [FleetCorrectiveMaintenanceStatus::Repaired->value(), FleetCorrectiveMaintenanceStatus::Closed->value()])
            ->get(['supplier_workshop', 'real_cost'])
            ->groupBy('supplier_workshop')
            ->map(fn (Collection $rows): array => [
                'supplier' => (string) $rows->first()?->supplier_workshop,
                'total' => (float) $rows->sum(fn ($r) => (float) $r->real_cost),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, float>
     */
    public function comparisonPreventiveVsCorrective(?int $companyId = null): array
    {
        $data = $this->build($companyId);

        return [
            'preventivo' => $data['preventivo_total'],
            'correctivo' => $data['correctivo_total'],
        ];
    }

    public function consumedSparesValue(?int $companyId = null): float
    {
        return (float) FleetSparePartMovement::query()
            ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
            ->where('direction', FleetSparePartMovementDirection::Outbound->value())
            ->sum('total_amount');
    }

    /**
     * @return array<string, int>
     */
    public function technicalInspectionTotals(?int $companyId = null): array
    {
        return [
            'expired' => FleetTechnicalInspection::query()
                ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
                ->where('status', FleetTechnicalInspectionStatus::Expired->value())
                ->count(),
            'valid' => FleetTechnicalInspection::query()
                ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
                ->where('status', FleetTechnicalInspectionStatus::Valid->value())
                ->count(),
        ];
    }
}
