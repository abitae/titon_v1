<?php

namespace App\Services\Mechanics;

use App\Enums\FleetEquipmentOperationalStatus;
use App\Enums\FleetPreventiveMaintenanceStatus;
use App\Enums\FleetTechnicalInspectionStatus;
use App\Enums\FleetWorkOrderStatus;
use App\Models\FleetEquipment;
use App\Models\FleetPreventiveMaintenance;
use App\Models\FleetTechnicalInspection;
use App\Models\FleetWorkOrder;
use App\Models\User;

class MechanicalDashboardAnalytics
{
    public function __construct(
        protected MechanicalCostAnalytics $costAnalytics,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        unset($user);

        $costSeries = $this->costAnalytics->build(null);

        $equipmentBase = FleetEquipment::query();

        $kpis = [
            'total_equipment' => (clone $equipmentBase)->count(),
            'operational' => (clone $equipmentBase)->where('operational_status', FleetEquipmentOperationalStatus::Operational->value())->count(),
            'in_maintenance' => (clone $equipmentBase)->where('operational_status', FleetEquipmentOperationalStatus::InMaintenance->value())->count(),
            'broken' => (clone $equipmentBase)->where('operational_status', FleetEquipmentOperationalStatus::Broken->value())->count(),
            'technical_expired' => FleetTechnicalInspection::query()->where('status', FleetTechnicalInspectionStatus::Expired->value())->count(),
            'technical_due_soon' => FleetTechnicalInspection::query()->where('status', FleetTechnicalInspectionStatus::DueSoon->value())->count(),
            'preventive_upcoming' => FleetPreventiveMaintenance::query()->whereIn('status', [
                FleetPreventiveMaintenanceStatus::Scheduled->value(),
                FleetPreventiveMaintenanceStatus::Pending->value(),
                FleetPreventiveMaintenanceStatus::Rescheduled->value(),
            ])
                ->whereDate('scheduled_date', '>=', today())
                ->whereDate('scheduled_date', '<=', today()->copy()->addDays(30))
                ->count(),
            'work_orders_open' => FleetWorkOrder::query()->whereIn('status', FleetWorkOrderStatus::openStatuses())->count(),
            'maintenance_cost_total' => (float) (($costSeries['preventivo_total'] ?? 0) + ($costSeries['correctivo_total'] ?? 0)),
        ];

        $countsByStatusCollection = FleetEquipment::query()
            ->selectRaw('operational_status, count(*) as c')
            ->groupBy('operational_status')
            ->pluck('c', 'operational_status');

        $equipmentStatusCases = FleetEquipmentOperationalStatus::cases();

        $charts = [
            'equipment_by_status' => [
                'type' => 'doughnut',
                'data' => [
                    'labels' => array_map(
                        static fn (FleetEquipmentOperationalStatus $status): string => $status->label(),
                        $equipmentStatusCases,
                    ),
                    'datasets' => [
                        [
                            'label' => 'Equipos por estado operativo',
                            'data' => array_map(
                                static fn (FleetEquipmentOperationalStatus $status): int => (int) ($countsByStatusCollection[$status->value()] ?? 0),
                                $equipmentStatusCases,
                            ),
                            'backgroundColor' => ['#22c55e', '#f59e0b', '#64748b', '#ef4444', '#94a3b8'],
                        ],
                    ],
                ],
            ],
            'cost_by_month' => [
                'type' => 'line',
                'data' => [
                    'labels' => array_column($costSeries['monthly'], 'label'),
                    'datasets' => [
                        [
                            'label' => 'Costos S/',
                            'data' => array_map(fn ($m) => $m['total'], $costSeries['monthly']),
                            'borderColor' => '#0ea5e9',
                            'backgroundColor' => 'rgba(14,165,233,0.15)',
                            'fill' => true,
                            'tension' => 0.35,
                        ],
                    ],
                ],
            ],
            'maintenance_preventivo_vs_correctivo' => [
                'type' => 'bar',
                'data' => [
                    'labels' => array_column($costSeries['monthly'], 'label'),
                    'datasets' => [
                        [
                            'label' => 'Preventivo',
                            'data' => array_map(fn ($m) => $m['preventivo'], $costSeries['monthly']),
                            'backgroundColor' => '#22d3ee',
                        ],
                        [
                            'label' => 'Correctivo',
                            'data' => array_map(fn ($m) => $m['correctivo'], $costSeries['monthly']),
                            'backgroundColor' => '#f97316',
                        ],
                    ],
                ],
            ],
            'technical_expired_vs_valid' => [
                'type' => 'bar',
                'data' => [
                    'labels' => ['Vencidas', 'Vigentes'],
                    'datasets' => [
                        [
                            'label' => 'Revisiones',
                            'data' => [
                                FleetTechnicalInspection::query()->where('status', FleetTechnicalInspectionStatus::Expired->value())->count(),
                                FleetTechnicalInspection::query()->where('status', FleetTechnicalInspectionStatus::Valid->value())->count(),
                            ],
                            'backgroundColor' => ['#ef4444', '#22c55e'],
                        ],
                    ],
                ],
            ],
            'cost_by_equipment' => [
                'type' => 'bar',
                'data' => [
                    'labels' => array_column(array_slice($costSeries['by_equipment'], 0, 8), 'label'),
                    'datasets' => [
                        [
                            'label' => 'S/ acumulado',
                            'data' => array_column(array_slice($costSeries['by_equipment'], 0, 8), 'total'),
                            'backgroundColor' => '#6366f1',
                        ],
                    ],
                ],
            ],
            'work_orders_by_status' => [
                'type' => 'bar',
                'data' => [
                    'labels' => array_map(
                        static fn (FleetWorkOrderStatus $status): string => $status->label(),
                        FleetWorkOrderStatus::cases(),
                    ),
                    'datasets' => [
                        [
                            'label' => 'OT abiertas y cerradas',
                            'data' => array_map(
                                static fn (FleetWorkOrderStatus $status): int => FleetWorkOrder::query()
                                    ->where('status', $status->value())
                                    ->count(),
                                FleetWorkOrderStatus::cases(),
                            ),
                            'backgroundColor' => '#38bdf8',
                        ],
                    ],
                ],
            ],
        ];

        return [
            'kpis' => $kpis,
            'charts' => $charts,
            'alerts' => $this->alerts(),
        ];
    }

    /**
     * @return array<int, array{type:string, message:string}>
     */
    protected function alerts(): array
    {
        $alerts = [];

        $expired = FleetTechnicalInspection::query()
            ->where('status', FleetTechnicalInspectionStatus::Expired->value())
            ->count();

        if ($expired > 0) {
            $alerts[] = ['type' => 'danger', 'message' => "Hay {$expired} revision(es) técnica(s) vencidas."];
        }

        $dueSoon = FleetTechnicalInspection::query()
            ->where('status', FleetTechnicalInspectionStatus::DueSoon->value())
            ->count();

        if ($dueSoon > 0) {
            $alerts[] = ['type' => 'warning', 'message' => "Hay {$dueSoon} revision(es) por vencer en 30 días."];
        }

        $observed = FleetTechnicalInspection::query()
            ->where('status', FleetTechnicalInspectionStatus::Observed->value())
            ->count();

        if ($observed > 0) {
            $alerts[] = ['type' => 'warning', 'message' => "Hay {$observed} revision(es) observadas."];
        }

        return $alerts;
    }
}
