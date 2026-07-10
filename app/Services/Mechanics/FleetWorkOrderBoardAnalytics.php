<?php

namespace App\Services\Mechanics;

use App\Enums\DocumentPriority;
use App\Enums\FleetWorkOrderStatus;
use App\Enums\FleetWorkOrderType;
use App\Models\FleetWorkOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FleetWorkOrderBoardAnalytics
{
    /**
     * @param  Builder<FleetWorkOrder>  $baseQuery
     * @param  Collection<int, array{user: User, open: int}>  $technicianLoads
     * @return array<string, array<string, mixed>>
     */
    public function charts(Builder $baseQuery, Collection $technicianLoads): array
    {
        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $priorityCounts = (clone $baseQuery)
            ->selectRaw('priority, count(*) as aggregate')
            ->groupBy('priority')
            ->pluck('aggregate', 'priority');

        $typeCounts = (clone $baseQuery)
            ->selectRaw('type, count(*) as aggregate')
            ->groupBy('type')
            ->pluck('aggregate', 'type');

        $costTotals = (clone $baseQuery)
            ->selectRaw('coalesce(sum(labor_cost), 0) as labor, coalesce(sum(spare_parts_cost), 0) as spares')
            ->first();

        $statusCases = FleetWorkOrderStatus::cases();
        $priorityCases = DocumentPriority::cases();
        $typeCases = FleetWorkOrderType::cases();

        return [
            'by_status' => [
                'type' => 'doughnut',
                'data' => [
                    'labels' => array_map(
                        static fn (FleetWorkOrderStatus $status): string => $status->label(),
                        $statusCases,
                    ),
                    'datasets' => [
                        [
                            'label' => 'OT por estado',
                            'data' => array_map(
                                static fn (FleetWorkOrderStatus $status): int => (int) ($statusCounts[$status->value()] ?? 0),
                                $statusCases,
                            ),
                            'backgroundColor' => ['#38bdf8', '#818cf8', '#22d3ee', '#fbbf24', '#34d399', '#64748b', '#f87171'],
                        ],
                    ],
                ],
            ],
            'by_priority' => [
                'type' => 'bar',
                'data' => [
                    'labels' => array_map(
                        static fn (DocumentPriority $priority): string => $priority->label(),
                        $priorityCases,
                    ),
                    'datasets' => [
                        [
                            'label' => 'OT',
                            'data' => array_map(
                                static fn (DocumentPriority $priority): int => (int) ($priorityCounts[$priority->value()] ?? 0),
                                $priorityCases,
                            ),
                            'backgroundColor' => ['#94a3b8', '#38bdf8', '#fbbf24', '#ef4444'],
                        ],
                    ],
                ],
                'options' => ['indexAxis' => 'y'],
            ],
            'by_type' => [
                'type' => 'doughnut',
                'data' => [
                    'labels' => array_map(
                        static fn (FleetWorkOrderType $type): string => $type->label(),
                        $typeCases,
                    ),
                    'datasets' => [
                        [
                            'label' => 'OT por tipo',
                            'data' => array_map(
                                static fn (FleetWorkOrderType $type): int => (int) ($typeCounts[$type->value()] ?? 0),
                                $typeCases,
                            ),
                            'backgroundColor' => ['#22d3ee', '#fb923c', '#a78bfa', '#34d399'],
                        ],
                    ],
                ],
            ],
            'technician_load' => [
                'type' => 'bar',
                'data' => [
                    'labels' => $technicianLoads->map(fn (array $row): string => $row['user']->name)->all(),
                    'datasets' => [
                        [
                            'label' => 'OT abiertas',
                            'data' => $technicianLoads->map(fn (array $row): int => $row['open'])->all(),
                            'backgroundColor' => '#6366f1',
                        ],
                    ],
                ],
                'options' => ['indexAxis' => 'y'],
            ],
            'cost_mix' => [
                'type' => 'doughnut',
                'data' => [
                    'labels' => ['Mano de obra', 'Repuestos'],
                    'datasets' => [
                        [
                            'label' => 'S/',
                            'data' => [
                                (float) ($costTotals->labor ?? 0),
                                (float) ($costTotals->spares ?? 0),
                            ],
                            'backgroundColor' => ['#0ea5e9', '#f97316'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
