<?php

namespace App\Livewire\Mechanics;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithFleetEquipmentSearch;
use App\Concerns\InteractsWithToast;
use App\Concerns\ViewsPdfInModal;
use App\Enums\CorrelativeSubject;
use App\Enums\DocumentPriority;
use App\Enums\FleetWorkOrderStatus;
use App\Enums\FleetWorkOrderType;
use App\Models\FleetCorrectiveMaintenance;
use App\Models\FleetEquipment;
use App\Models\FleetPreventiveMaintenance;
use App\Models\FleetTechnicalInspection;
use App\Models\FleetWorkOrder;
use App\Models\Project;
use App\Models\User;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use App\Services\Mechanics\FleetWorkOrderBoardAnalytics;
use App\Services\Mechanics\FleetWorkOrderBoardQuery;
use App\Support\DefaultDate;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageFleetWorkOrders extends Component
{
    use InteractsWithFleetEquipmentSearch, InteractsWithToast, ViewsPdfInModal, WithFileUploads, WithPagination;

    public function openWorkOrdersReportPdf(string $report): void
    {
        $reports = [
            'detail' => ['mechanics.report.work-orders.pdf', 'Ordenes de trabajo · Detalle'],
            'by-technician' => ['mechanics.report.work-orders.by-technician.pdf', 'Ordenes de trabajo · Por tecnico'],
            'by-project' => ['mechanics.report.work-orders.by-project.pdf', 'Ordenes de trabajo · Por obra'],
            'by-equipment' => ['mechanics.report.work-orders.by-equipment.pdf', 'Ordenes de trabajo · Por equipo'],
            'overdue' => ['mechanics.report.work-orders.overdue.pdf', 'Ordenes de trabajo · Vencidas'],
            'costs' => ['mechanics.report.work-orders.costs.pdf', 'Ordenes de trabajo · Costos'],
            'types' => ['mechanics.report.work-orders.types.pdf', 'Ordenes de trabajo · Tipos'],
        ];

        abort_unless(isset($reports[$report]), 404);

        [$routeName, $title] = $reports[$report];

        $this->openRoutePdfModal($routeName, $title, $this->exportQueryParams());
    }

    public string $title = 'Ordenes de trabajo';

    /** @var 'graficos'|'kanban'|'list'|'resources'|'calendar' */
    public string $viewTab = 'graficos';

    /** ISO date (Y-m-d) prefilled when creating OT from calendar. */
    public string $calendarPrefillDate = '';

    public string $search = '';

    public ?int $filter_project_id = null;

    public ?int $filter_equipment_id = null;

    public ?int $filter_technician_id = null;

    public string $filter_type = '';

    public string $filter_status = '';

    public string $filter_priority = '';

    public string $filter_scheduled_from = '';

    public string $filter_scheduled_to = '';

    public string $filter_closed_from = '';

    public string $filter_closed_to = '';

    public bool $filter_overdue_only = false;

    public string $sortColumn = 'issued_at';

    public string $sortDirection = 'desc';

    /** @var list<int> */
    public array $selectedIds = [];

    public string $bulkTargetStatus = '';

    public string $calendarMonth = '';

    public bool $showFormModal = false;

    public bool $showAssignModal = false;

    public ?int $editingId = null;

    public ?int $assignWorkOrderId = null;

    public ?int $assignResponsibleUserId = null;

    public string $code = '';

    public ?int $fleet_equipment_id = null;

    public ?int $work_project_id = null;

    public string $type = '';

    public string $priority = '';

    public string $status = '';

    public string $issued_at = '';

    public string $scheduled_date = '';

    public string $work_description = '';

    public string $diagnosis = '';

    public string $parts_used_description = '';

    public string $labor_cost = '';

    public string $spare_parts_cost = '';

    public ?int $responsible_user_id = null;

    public ?int $fleet_preventive_maintenance_id = null;

    public ?int $fleet_corrective_maintenance_id = null;

    public ?int $fleet_technical_inspection_id = null;

    public array $attachments = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.ver'), 403);
        $this->type = FleetWorkOrderType::Preventive->value();
        $this->priority = DocumentPriority::Medium->value();
        $this->status = FleetWorkOrderStatus::Generated->value();
        $this->issued_at = DefaultDate::today();
        $this->scheduled_date = DefaultDate::today();
        $this->calendarMonth = now()->format('Y-m');
        $range = DefaultDate::filterRange();
        $this->filter_scheduled_from = $range['from'];
        $this->filter_scheduled_to = $range['to'];
        $this->filter_closed_from = $range['from'];
        $this->filter_closed_to = $range['to'];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selectedIds = [];
    }

    public function updatedFilterProjectId(): void
    {
        $this->resetPage();
        $this->selectedIds = [];
    }

    public function updatedFilterEquipmentId(): void
    {
        $this->syncFilterFleetEquipmentSearch();
        $this->afterFilterFleetEquipmentChanged();
    }

    protected function afterFilterFleetEquipmentChanged(): void
    {
        $this->resetPage();
        $this->selectedIds = [];
    }

    public function updatedFilterTechnicianId(): void
    {
        $this->resetPage();
        $this->selectedIds = [];
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
        $this->selectedIds = [];
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
        $this->selectedIds = [];
    }

    public function updatedFilterPriority(): void
    {
        $this->resetPage();
        $this->selectedIds = [];
    }

    public function updatedFilterScheduledFrom(): void
    {
        $this->resetPage();
    }

    public function updatedFilterScheduledTo(): void
    {
        $this->resetPage();
    }

    public function updatedFilterClosedFrom(): void
    {
        $this->resetPage();
    }

    public function updatedFilterClosedTo(): void
    {
        $this->resetPage();
    }

    public function updatedFilterOverdueOnly(): void
    {
        $this->resetPage();
        $this->selectedIds = [];
    }

    public function updatedFleetEquipmentId(?int $value): void
    {
        unset($value);
        $this->fleet_preventive_maintenance_id = null;
        $this->fleet_corrective_maintenance_id = null;
        $this->fleet_technical_inspection_id = null;
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['graficos', 'kanban', 'list', 'resources', 'calendar'], true)) {
            return;
        }

        $this->viewTab = $tab;
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        $allowed = ['code', 'issued_at', 'scheduled_date', 'closed_at', 'total_cost', 'status', 'priority', 'type'];

        if (! in_array($column, $allowed, true)) {
            return;
        }

        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSortColumn(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'filter_project_id',
            'filter_equipment_id',
            'filter_technician_id',
            'filter_type',
            'filter_status',
            'filter_priority',
            'filter_scheduled_from',
            'filter_scheduled_to',
            'filter_closed_from',
            'filter_closed_to',
        ]);
        $this->filter_overdue_only = false;
        $this->filter_equipment_search = '';
        $this->selectedIds = [];
        $this->resetPage();
    }

    public function toggleSelectAllOnPage(): void
    {
        $paginator = $this->listRowsQuery()->paginate(15);

        /** @var list<int> $ids */
        $ids = $paginator->pluck('id')->map(fn ($id): int => (int) $id)->all();

        if ($ids === []) {
            return;
        }

        $allSelected = array_diff($ids, $this->selectedIds) === [];

        if ($allSelected) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, $ids));
        } else {
            $this->selectedIds = array_values(array_unique([...$this->selectedIds, ...$ids]));
        }
    }

    public function updatedSelectedIds(): void
    {
        $this->selectedIds = array_values(array_unique(array_filter(array_map('intval', $this->selectedIds))));
    }

    public function kanbanMove(int $workOrderId, string $newStatus): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.crear'), 403);

        if (! in_array($newStatus, FleetWorkOrderStatus::values(), true)) {
            return;
        }

        $workOrder = FleetWorkOrder::query()->findOrFail($workOrderId);

        if ((string) $workOrder->status === $newStatus) {
            return;
        }

        $payload = ['status' => $newStatus];

        if (in_array($newStatus, [FleetWorkOrderStatus::Closed->value(), FleetWorkOrderStatus::Finished->value()], true)) {
            $payload['closed_at'] = $workOrder->closed_at ?? now();
        } elseif ($newStatus !== FleetWorkOrderStatus::Cancelled->value()) {
            $payload['closed_at'] = null;
        }

        $workOrder->update($payload);

        $this->successToast('Estado de la OT actualizado.');
    }

    public function openAssignModal(int $workOrderId): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.crear'), 403);

        $row = FleetWorkOrder::query()->findOrFail($workOrderId);
        $this->assignWorkOrderId = $row->id;
        $this->assignResponsibleUserId = $row->responsible_user_id;
        $this->showAssignModal = true;
    }

    public function saveAssignment(): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.crear'), 403);

        $validated = $this->validate([
            'assignResponsibleUserId' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $workOrder = FleetWorkOrder::query()->findOrFail((int) $this->assignWorkOrderId);

        $payload = [
            'responsible_user_id' => $validated['assignResponsibleUserId'],
        ];

        if ($workOrder->status === FleetWorkOrderStatus::Generated->value() && $validated['assignResponsibleUserId'] !== null) {
            $payload['status'] = FleetWorkOrderStatus::Assigned->value();
        }

        $workOrder->update($payload);

        $this->showAssignModal = false;
        $this->assignWorkOrderId = null;
        $this->successToast('Asignacion actualizada.');
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->assignWorkOrderId = null;
    }

    public function bulkApplyStatus(): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.crear'), 403);

        if ($this->selectedIds === []) {
            $this->warningToast('Seleccione al menos una OT.');

            return;
        }

        $validated = $this->validate([
            'bulkTargetStatus' => ['required', Rule::in(FleetWorkOrderStatus::values())],
        ]);

        DB::transaction(function () use ($validated): void {
            $orders = FleetWorkOrder::query()->whereKey($this->selectedIds)->get();

            foreach ($orders as $workOrder) {
                $payload = ['status' => $validated['bulkTargetStatus']];

                if (in_array($validated['bulkTargetStatus'], [FleetWorkOrderStatus::Closed->value(), FleetWorkOrderStatus::Finished->value()], true)) {
                    $payload['closed_at'] = $workOrder->closed_at ?? now();
                } elseif ($validated['bulkTargetStatus'] !== FleetWorkOrderStatus::Cancelled->value()) {
                    $payload['closed_at'] = null;
                }

                $workOrder->update($payload);
            }
        });

        $this->selectedIds = [];
        $this->bulkTargetStatus = '';
        $this->successToast('OT actualizadas en bloque.');
    }

    /**
     * @return array<string, mixed>
     */
    public function exportQueryParams(): array
    {
        return array_filter([
            'search' => $this->search,
            'work_project_id' => $this->filter_project_id,
            'fleet_equipment_id' => $this->filter_equipment_id,
            'responsible_user_id' => $this->filter_technician_id,
            'type' => $this->filter_type !== '' ? $this->filter_type : null,
            'status' => $this->filter_status !== '' ? $this->filter_status : null,
            'priority' => $this->filter_priority !== '' ? $this->filter_priority : null,
            'scheduled_from' => $this->filter_scheduled_from !== '' ? $this->filter_scheduled_from : null,
            'scheduled_to' => $this->filter_scheduled_to !== '' ? $this->filter_scheduled_to : null,
            'closed_from' => $this->filter_closed_from !== '' ? $this->filter_closed_from : null,
            'closed_to' => $this->filter_closed_to !== '' ? $this->filter_closed_to : null,
            'overdue_only' => $this->filter_overdue_only ? '1' : null,
            'sort' => $this->sortColumn,
            'dir' => $this->sortDirection,
        ], fn (mixed $v): bool => $v !== null && $v !== '' && $v !== false);
    }

    public function render(FleetWorkOrderBoardAnalytics $boardAnalytics): View
    {
        $equipmentId = $this->fleet_equipment_id;
        $base = $this->filteredBaseQuery();

        $kpis = [
            'open' => (clone $base)->whereIn('status', FleetWorkOrderStatus::openStatuses())->count(),
            'in_progress' => (clone $base)->where('status', FleetWorkOrderStatus::InProgress->value())->count(),
            'overdue' => (clone $base)->scheduledOverdue()->count(),
            'finished_closed' => (clone $base)->whereIn('status', [
                FleetWorkOrderStatus::Finished->value(),
                FleetWorkOrderStatus::Closed->value(),
            ])->count(),
            'total_cost_period' => (float) (clone $base)->sum('total_cost'),
        ];

        $avgAttentionDays = $this->averageAttentionDays();

        $statsFilters = $this->filterPayload();
        $statsFilters['responsible_user_id'] = null;

        $topLoadRows = (clone FleetWorkOrder::query())
            ->tap(fn (Builder $query) => FleetWorkOrderBoardQuery::apply($query, $statsFilters))
            ->selectRaw('responsible_user_id, COUNT(*) as open_count')
            ->whereNotNull('responsible_user_id')
            ->whereIn('status', FleetWorkOrderStatus::openStatuses())
            ->groupBy('responsible_user_id')
            ->orderByDesc('open_count')
            ->limit(5)
            ->get();

        $topLoadUsers = User::query()
            ->whereIn('id', $topLoadRows->pluck('responsible_user_id'))
            ->get()
            ->keyBy('id');

        $topLoads = $topLoadRows
            ->map(function ($row) use ($topLoadUsers): ?array {
                $user = $topLoadUsers->get($row->responsible_user_id);

                if ($user === null) {
                    return null;
                }

                return ['user' => $user, 'open' => (int) $row->open_count];
            })
            ->filter()
            ->values();

        $kanbanGrouped = [];
        $kanbanStatuses = FleetWorkOrderStatus::cases();

        if ($this->viewTab === 'kanban') {
            $kanbanOrders = (clone $base)
                ->with(['equipment', 'workProject', 'responsibleUser'])
                ->orderByRaw("CASE priority WHEN 'critica' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 ELSE 4 END")
                ->orderByDesc('issued_at')
                ->limit(400)
                ->get();

            $kanbanGrouped = Collection::wrap($kanbanStatuses)
                ->mapWithKeys(fn (FleetWorkOrderStatus $status): array => [$status->value() => $kanbanOrders->where('status', $status->value())->values()])
                ->all();
        }

        $listRows = $this->viewTab === 'list'
            ? $this->listRowsQuery()->paginate(15)
            : null;

        $boardCharts = $this->viewTab === 'graficos'
            ? $boardAnalytics->charts($base, $topLoads)
            : [];

        $totalFiltered = (int) (clone $base)->count();
        $kpiPercents = [
            'open' => $totalFiltered > 0 ? round(($kpis['open'] / $totalFiltered) * 100, 1) : 0,
            'in_progress' => $totalFiltered > 0 ? round(($kpis['in_progress'] / $totalFiltered) * 100, 1) : 0,
            'overdue' => $totalFiltered > 0 ? round(($kpis['overdue'] / $totalFiltered) * 100, 1) : 0,
            'finished_closed' => $totalFiltered > 0 ? round(($kpis['finished_closed'] / $totalFiltered) * 100, 1) : 0,
        ];

        $technicianStats = collect();

        if ($this->viewTab === 'resources') {
            $technicianStats = $this->responsibleUsers()->map(function (User $user) use ($statsFilters): array {
                $openQuery = FleetWorkOrder::query()->where('responsible_user_id', $user->id);
                FleetWorkOrderBoardQuery::apply($openQuery, $statsFilters);
                $open = (clone $openQuery)->whereIn('status', FleetWorkOrderStatus::openStatuses())->count();

                $overdueQuery = FleetWorkOrder::query()->where('responsible_user_id', $user->id);
                FleetWorkOrderBoardQuery::apply($overdueQuery, $statsFilters);
                $overdue = (clone $overdueQuery)->scheduledOverdue()->count();

                $pendingQuery = FleetWorkOrder::query()->where('responsible_user_id', $user->id);
                FleetWorkOrderBoardQuery::apply($pendingQuery, $statsFilters);

                /** @var Collection<int, FleetWorkOrder> $pending */
                $pending = (clone $pendingQuery)
                    ->whereIn('status', FleetWorkOrderStatus::openStatuses())
                    ->with(['equipment', 'workProject'])
                    ->orderBy('scheduled_date')
                    ->limit(8)
                    ->get();

                return compact('user', 'open', 'overdue', 'pending');
            });
        }

        $calendarWorkOrders = collect();
        $calendarPreventive = collect();
        $calendarInspections = collect();
        $calendarStart = now()->startOfMonth();
        $calendarEnd = now()->endOfMonth();

        if ($this->viewTab === 'calendar') {
            $parts = explode('-', $this->calendarMonth);
            if (count($parts) !== 2) {
                $this->calendarMonth = now()->format('Y-m');
                $parts = explode('-', $this->calendarMonth);
            }

            $calendarYear = (int) ($parts[0] ?? now()->year);
            $calendarMonthNum = (int) ($parts[1] ?? now()->month);

            $calendarStart = Carbon::create($calendarYear, $calendarMonthNum, 1)->startOfMonth();
            $calendarEnd = (clone $calendarStart)->endOfMonth();

            $calendarWorkOrders = (clone $base)
                ->with(['equipment', 'responsibleUser'])
                ->whereNotNull('scheduled_date')
                ->whereBetween('scheduled_date', [$calendarStart->toDateString(), $calendarEnd->toDateString()])
                ->orderBy('scheduled_date')
                ->get();

            $calendarPreventive = FleetPreventiveMaintenance::query()
                ->with('equipment')
                ->whereBetween('scheduled_date', [$calendarStart->toDateString(), $calendarEnd->toDateString()])
                ->orderBy('scheduled_date')
                ->get();

            $calendarInspections = FleetTechnicalInspection::query()
                ->with('equipment')
                ->whereBetween('due_at', [$calendarStart->toDateString(), $calendarEnd->toDateString()])
                ->orderBy('due_at')
                ->get();
        }

        $preventives = collect();
        $correctives = collect();
        $inspections = collect();

        if ($this->showFormModal && $equipmentId) {
            $preventives = FleetPreventiveMaintenance::query()
                ->where('fleet_equipment_id', $equipmentId)
                ->latest()
                ->limit(40)
                ->get();
            $correctives = FleetCorrectiveMaintenance::query()
                ->where('fleet_equipment_id', $equipmentId)
                ->latest()
                ->limit(40)
                ->get();
            $inspections = FleetTechnicalInspection::query()
                ->where('fleet_equipment_id', $equipmentId)
                ->latest()
                ->limit(40)
                ->get();
        }

        return view('livewire.mechanics.manage-fleet-work-orders', [
            'exportParams' => $this->exportQueryParams(),
            'kanbanGrouped' => $kanbanGrouped,
            'kanbanStatuses' => $kanbanStatuses,
            'kpis' => $kpis,
            'kpiPercents' => $kpiPercents,
            'totalFiltered' => $totalFiltered,
            'boardCharts' => $boardCharts,
            'topTechnicianLoads' => $topLoads,
            'avgAttentionDays' => $avgAttentionDays,
            'technicianStats' => $technicianStats,
            'calendarWorkOrders' => $calendarWorkOrders,
            'calendarPreventive' => $calendarPreventive,
            'calendarInspections' => $calendarInspections,
            'calendarStart' => $calendarStart,
            'calendarEnd' => $calendarEnd,
            'listRows' => $listRows,
            'equipmentFormOptions' => $this->fleetEquipmentSelectOptions(),
            'equipmentFilterOptions' => $this->fleetEquipmentFilterOptions(),
            'projects' => Project::query()->select(['id', 'code', 'name'])->orderBy('code')->get(),
            'responsibleUsers' => $this->responsibleUsers(),
            'types' => FleetWorkOrderType::cases(),
            'statuses' => FleetWorkOrderStatus::cases(),
            'priorities' => DocumentPriority::cases(),
            'preventives' => $preventives,
            'correctives' => $correctives,
            'inspections' => $inspections,
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function openCreate(ResolveCurrentCompany $resolveCurrentCompany): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.crear'), 403);
        $company = $resolveCurrentCompany->handle(auth()->user());
        abort_if($company === null, 403);
        $this->resetForm();
        $this->fleet_equipment_id = FleetEquipment::query()->orderBy('internal_code')->value('id');
        if ($this->fleet_equipment_id === null) {
            $this->dangerToast('Registre primero un equipo.');

            return;
        }

        $this->code = app(IssueCompanyCorrelativeCode::class)->peek($company, CorrelativeSubject::FleetWorkOrder);
        $this->issued_at = DefaultDate::today();
        $this->syncFleetEquipmentSearch();
        if ($this->calendarPrefillDate !== '') {
            $this->scheduled_date = $this->calendarPrefillDate;
            $this->calendarPrefillDate = '';
        }
        $this->showFormModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.crear'), 403);
        $row = FleetWorkOrder::query()->findOrFail($id);
        $this->editingId = $row->id;
        $this->code = $row->code;
        $this->fleet_equipment_id = $row->fleet_equipment_id;
        $this->work_project_id = $row->work_project_id;
        $this->type = $row->type;
        $this->priority = $row->priority;
        $this->status = $row->status;
        $this->issued_at = $row->issued_at?->format('Y-m-d') ?? '';
        $this->scheduled_date = $row->scheduled_date?->format('Y-m-d') ?? '';
        $this->work_description = (string) ($row->work_description ?? '');
        $this->diagnosis = (string) ($row->diagnosis ?? '');
        $this->parts_used_description = (string) ($row->parts_used_description ?? '');
        $this->labor_cost = (string) $row->labor_cost;
        $this->spare_parts_cost = (string) $row->spare_parts_cost;
        $this->responsible_user_id = $row->responsible_user_id;
        $this->fleet_preventive_maintenance_id = $row->fleet_preventive_maintenance_id;
        $this->fleet_corrective_maintenance_id = $row->fleet_corrective_maintenance_id;
        $this->fleet_technical_inspection_id = $row->fleet_technical_inspection_id;
        $this->attachments = [];
        $this->syncFleetEquipmentSearch();
        $this->showFormModal = true;
    }

    public function save(ResolveCurrentCompany $resolveCurrentCompany): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.crear'), 403);
        $company = $resolveCurrentCompany->handle(auth()->user());
        abort_if($company === null, 403);

        $validated = $this->validate([
            'code' => [
                Rule::requiredIf($this->editingId !== null),
                'nullable',
                'string',
                'max:40',
                Rule::unique('fleet_work_orders', 'code')->where(fn ($q) => $q->where('company_id', $company->id))->ignore($this->editingId),
            ],
            'fleet_equipment_id' => ['required', Rule::exists('fleet_equipments', 'id')->where(fn ($q) => $q->where('company_id', $company->id))],
            'work_project_id' => [
                'nullable',
                Rule::exists('projects', 'id')->where(fn ($query) => $query->where('company_id', $company->id)),
            ],
            'type' => ['required', Rule::in(array_map(fn ($t) => $t->value(), FleetWorkOrderType::cases()))],
            'priority' => ['required', Rule::in(DocumentPriority::values())],
            'status' => ['required', Rule::in(array_map(fn ($s) => $s->value(), FleetWorkOrderStatus::cases()))],
            'issued_at' => ['required', 'date'],
            'scheduled_date' => ['nullable', 'date'],
            'work_description' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'parts_used_description' => ['nullable', 'string'],
            'labor_cost' => ['required', 'numeric', 'min:0'],
            'spare_parts_cost' => ['required', 'numeric', 'min:0'],
            'responsible_user_id' => ['nullable', 'exists:users,id'],
            'fleet_preventive_maintenance_id' => ['nullable', 'exists:fleet_preventive_maintenances,id'],
            'fleet_corrective_maintenance_id' => ['nullable', 'exists:fleet_corrective_maintenances,id'],
            'fleet_technical_inspection_id' => ['nullable', 'exists:fleet_technical_inspections,id'],
            'attachments.*' => ['nullable', 'file', 'max:10240'],
        ]);

        $resolvedCode = $this->editingId
            ? (string) $validated['code']
            : app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::FleetWorkOrder);

        $payload = [
            'company_id' => $company->id,
            'fleet_equipment_id' => $validated['fleet_equipment_id'],
            'work_project_id' => $validated['work_project_id'],
            'code' => $resolvedCode,
            'type' => $validated['type'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'issued_at' => $validated['issued_at'],
            'scheduled_date' => $validated['scheduled_date'] ?: null,
            'work_description' => $validated['work_description'] ?: null,
            'diagnosis' => $validated['diagnosis'] ?: null,
            'parts_used_description' => $validated['parts_used_description'] ?: null,
            'labor_cost' => $validated['labor_cost'],
            'spare_parts_cost' => $validated['spare_parts_cost'],
            'responsible_user_id' => $validated['responsible_user_id'],
            'fleet_preventive_maintenance_id' => $validated['fleet_preventive_maintenance_id'],
            'fleet_corrective_maintenance_id' => $validated['fleet_corrective_maintenance_id'],
            'fleet_technical_inspection_id' => $validated['fleet_technical_inspection_id'],
        ];

        if (in_array($validated['status'], [FleetWorkOrderStatus::Closed->value(), FleetWorkOrderStatus::Finished->value()], true)) {
            $existing = $this->editingId ? FleetWorkOrder::query()->find($this->editingId)?->closed_at : null;
            $payload['closed_at'] = $existing ?? now();
        } else {
            $payload['closed_at'] = null;
        }

        if ($this->editingId) {
            $model = FleetWorkOrder::query()->findOrFail($this->editingId);
            $model->update($payload);
        } else {
            $model = FleetWorkOrder::query()->create($payload);
        }

        foreach ($this->attachments as $uploadedFile) {
            $model->addMedia($uploadedFile->getRealPath())
                ->usingFileName($uploadedFile->getClientOriginalName())
                ->toMediaCollection('technical_reports', 'public');
        }

        $this->resetForm();
        $this->successToast('OT guardada.');
    }

    public function startCreateFromCalendar(string $date): void
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return;
        }

        $this->calendarPrefillDate = $date;
        $this->openCreate(app(ResolveCurrentCompany::class));
    }

    public function closeOrder(int $id): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.cerrar'), 403);
        FleetWorkOrder::query()->findOrFail($id)->update([
            'status' => FleetWorkOrderStatus::Closed->value(),
            'closed_at' => now(),
        ]);
        $this->successToast('OT cerrada.');
    }

    public function deleteRow(int $id): void
    {
        abort_unless(auth()->user()?->can('mecanica.eliminar'), 403);
        FleetWorkOrder::query()->findOrFail($id)->delete();
        $this->warningToast('OT eliminada.');
    }

    public function close(): void
    {
        $this->showFormModal = false;
    }

    /**
     * @return Builder<FleetWorkOrder>
     */
    protected function filteredBaseQuery(): Builder
    {
        $q = FleetWorkOrder::query()->with(['equipment', 'workProject', 'responsibleUser']);

        return FleetWorkOrderBoardQuery::apply($q, $this->filterPayload());
    }

    /**
     * @return Builder<FleetWorkOrder>
     */
    protected function listRowsQuery(): Builder
    {
        $q = $this->filteredBaseQuery();

        $column = $this->sortColumn;
        $dir = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        if (in_array($column, ['code', 'issued_at', 'scheduled_date', 'closed_at', 'total_cost', 'status', 'priority', 'type'], true)) {
            $q->orderBy($column, $dir);
        } else {
            $q->orderByDesc('issued_at');
        }

        return $q;
    }

    /**
     * @return array<string, mixed>
     */
    protected function filterPayload(): array
    {
        return [
            'search' => $this->search,
            'work_project_id' => $this->filter_project_id,
            'fleet_equipment_id' => $this->filter_equipment_id,
            'responsible_user_id' => $this->filter_technician_id,
            'type' => $this->filter_type !== '' ? $this->filter_type : null,
            'status' => $this->filter_status !== '' ? $this->filter_status : null,
            'priority' => $this->filter_priority !== '' ? $this->filter_priority : null,
            'scheduled_from' => $this->filter_scheduled_from !== '' ? $this->filter_scheduled_from : null,
            'scheduled_to' => $this->filter_scheduled_to !== '' ? $this->filter_scheduled_to : null,
            'closed_from' => $this->filter_closed_from !== '' ? $this->filter_closed_from : null,
            'closed_to' => $this->filter_closed_to !== '' ? $this->filter_closed_to : null,
            'overdue_only' => $this->filter_overdue_only,
        ];
    }

    protected function averageAttentionDays(): ?float
    {
        $query = FleetWorkOrder::query()
            ->whereNotNull('closed_at')
            ->whereNotNull('issued_at')
            ->whereIn('status', [FleetWorkOrderStatus::Closed->value(), FleetWorkOrderStatus::Finished->value()]);

        FleetWorkOrderBoardQuery::apply($query, $this->filterPayload());

        $days = $query->get()->map(function (FleetWorkOrder $order): float {
            $issued = $order->issued_at;
            $closed = $order->closed_at;

            if ($issued === null || $closed === null) {
                return 0.0;
            }

            return max(0, (float) $issued->diffInDays(Carbon::parse($closed)));
        });

        return $days->isNotEmpty() ? round((float) $days->avg(), 1) : null;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingId',
            'code',
            'fleet_equipment_id',
            'work_project_id',
            'scheduled_date',
            'work_description',
            'diagnosis',
            'parts_used_description',
            'labor_cost',
            'spare_parts_cost',
            'responsible_user_id',
            'fleet_preventive_maintenance_id',
            'fleet_corrective_maintenance_id',
            'fleet_technical_inspection_id',
            'attachments',
        ]);
        $this->type = FleetWorkOrderType::Preventive->value();
        $this->priority = DocumentPriority::Medium->value();
        $this->status = FleetWorkOrderStatus::Generated->value();
        $this->issued_at = DefaultDate::today();
        $this->scheduled_date = DefaultDate::today();
        $this->resetFleetEquipmentSearch();
        $this->showFormModal = false;
    }

    protected function responsibleUsers(): Collection
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        return $company?->users()->wherePivot('active', true)->orderBy('name')->get() ?? collect();
    }
}
