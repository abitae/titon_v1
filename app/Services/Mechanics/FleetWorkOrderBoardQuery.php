<?php

namespace App\Services\Mechanics;

use App\Enums\DocumentPriority;
use App\Enums\FleetWorkOrderStatus;
use App\Enums\FleetWorkOrderType;
use App\Models\FleetWorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FleetWorkOrderBoardQuery
{
    /**
     * @param  Builder<FleetWorkOrder>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<FleetWorkOrder>
     */
    public static function apply(Builder $query, array $filters): Builder
    {
        $search = isset($filters['search']) ? trim((string) $filters['search']) : '';

        return $query
            ->when($search !== '', function (Builder $q) use ($search): void {
                $q->where(function (Builder $inner) use ($search): void {
                    $inner->where('code', 'like', '%'.$search.'%')
                        ->orWhereHas('equipment', fn (Builder $eq) => $eq->where('name', 'like', '%'.$search.'%')
                            ->orWhere('internal_code', 'like', '%'.$search.'%'));
                });
            })
            ->when(! empty($filters['work_project_id']), fn (Builder $q) => $q->where('work_project_id', (int) $filters['work_project_id']))
            ->when(! empty($filters['fleet_equipment_id']), fn (Builder $q) => $q->where('fleet_equipment_id', (int) $filters['fleet_equipment_id']))
            ->when(! empty($filters['responsible_user_id']), fn (Builder $q) => $q->where('responsible_user_id', (int) $filters['responsible_user_id']))
            ->when(! empty($filters['type']), fn (Builder $q) => $q->where('type', (string) $filters['type']))
            ->when(! empty($filters['status']), fn (Builder $q) => $q->where('status', (string) $filters['status']))
            ->when(! empty($filters['priority']), fn (Builder $q) => $q->where('priority', (string) $filters['priority']))
            ->when(! empty($filters['scheduled_from']), fn (Builder $q) => $q->whereDate('scheduled_date', '>=', (string) $filters['scheduled_from']))
            ->when(! empty($filters['scheduled_to']), fn (Builder $q) => $q->whereDate('scheduled_date', '<=', (string) $filters['scheduled_to']))
            ->when(! empty($filters['closed_from']), fn (Builder $q) => $q->whereDate('closed_at', '>=', (string) $filters['closed_from']))
            ->when(! empty($filters['closed_to']), fn (Builder $q) => $q->whereDate('closed_at', '<=', (string) $filters['closed_to']))
            ->when(! empty($filters['overdue_only']), function (Builder $q): void {
                $q->scheduledOverdue();
            });
    }

    /**
     * @return array<string, mixed>
     */
    public static function filtersFromRequest(Request $request): array
    {
        return [
            'search' => $request->string('search')->toString(),
            'work_project_id' => $request->integer('work_project_id') ?: null,
            'fleet_equipment_id' => $request->integer('fleet_equipment_id') ?: null,
            'responsible_user_id' => $request->integer('responsible_user_id') ?: null,
            'type' => $request->filled('type') && in_array($request->string('type')->toString(), FleetWorkOrderType::values(), true)
                ? $request->string('type')->toString()
                : null,
            'status' => $request->filled('status') && in_array($request->string('status')->toString(), FleetWorkOrderStatus::values(), true)
                ? $request->string('status')->toString()
                : null,
            'priority' => $request->filled('priority') && in_array($request->string('priority')->toString(), DocumentPriority::values(), true)
                ? $request->string('priority')->toString()
                : null,
            'scheduled_from' => $request->filled('scheduled_from') ? $request->string('scheduled_from')->toString() : null,
            'scheduled_to' => $request->filled('scheduled_to') ? $request->string('scheduled_to')->toString() : null,
            'closed_from' => $request->filled('closed_from') ? $request->string('closed_from')->toString() : null,
            'closed_to' => $request->filled('closed_to') ? $request->string('closed_to')->toString() : null,
            'overdue_only' => $request->boolean('overdue_only'),
        ];
    }
}
