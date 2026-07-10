<div class="space-y-3" x-data="{ draggingId: null, showAdvancedFilters: false }">
    <x-mechanics.page-header :title="$title" description="Panel gráfico y tablero operativo de órdenes de trabajo.">
        @can('mantenimientos.crear')
            <flux:button variant="primary" size="sm" icon="plus" wire:click="openCreate">Nueva OT</flux:button>
        @endcan
    </x-mechanics.page-header>

    {{-- Filtros --}}
    <x-mechanics.filter-strip>
        <div class="min-w-[9rem] flex-1">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Búsqueda</label>
            <input wire:model.live.debounce.400ms="search" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Código OT, equipo…" />
        </div>
        <div class="w-28">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Obra</label>
            <select wire:model.live="filter_project_id" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-44">
            <x-mechanics.equipment-select
                label="Equipo"
                :options="$equipmentFilterOptions"
                :selected-value="$filter_equipment_id"
                search-model="filter_equipment_search"
                select-method="selectFilterFleetEquipment"
                clear-method="clearFilterFleetEquipment"
                placeholder="Buscar equipo..."
                :allow-clear="true"
            />
        </div>
        <div class="w-32">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Técnico</label>
            <select wire:model.live="filter_technician_id" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($responsibleUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-28">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Estado</label>
            <select wire:model.live="filter_status" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s->value() }}">{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" x-on:click="showAdvancedFilters = !showAdvancedFilters" class="h-8 self-end rounded-lg border border-slate-300 px-2 text-[11px] font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-900">
            <span x-text="showAdvancedFilters ? 'Menos filtros' : 'Más filtros'"></span>
        </button>
        <flux:button variant="ghost" size="sm" wire:click="resetFilters" type="button" class="self-end">Limpiar</flux:button>
    </x-mechanics.filter-strip>

    <div x-show="showAdvancedFilters" x-cloak class="flex flex-wrap items-end gap-2 rounded-lg border border-slate-200 bg-slate-50/80 px-2 py-1.5 dark:border-slate-800 dark:bg-slate-950/50">
        <div class="w-28">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Tipo</label>
            <select wire:model.live="filter_type" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($types as $t)
                    <option value="{{ $t->value() }}">{{ $t->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-28">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Prioridad</label>
            <select wire:model.live="filter_priority" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas</option>
                @foreach ($priorities as $p)
                    <option value="{{ $p->value() }}">{{ $p->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-32">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Prog. desde</label>
            <input type="date" wire:model.live="filter_scheduled_from" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        </div>
        <div class="w-32">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Prog. hasta</label>
            <input type="date" wire:model.live="filter_scheduled_to" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        </div>
        <div class="w-32">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Cierre desde</label>
            <input type="date" wire:model.live="filter_closed_from" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        </div>
        <div class="w-32">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Cierre hasta</label>
            <input type="date" wire:model.live="filter_closed_to" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        </div>
        <label class="flex h-8 cursor-pointer items-center gap-1.5 text-[11px] text-slate-700 dark:text-slate-300">
            <flux:checkbox wire:model.live="filter_overdue_only" />
            Solo vencidas
        </label>
    </div>

    {{-- Pestañas --}}
    <div class="flex flex-wrap gap-1 rounded-lg border border-slate-200 bg-slate-50/80 p-1 dark:border-slate-800 dark:bg-slate-950/50">
        <flux:button size="sm" variant="{{ $viewTab === 'graficos' ? 'primary' : 'ghost' }}" wire:click="setTab('graficos')" icon="chart-bar">Gráficos</flux:button>
        <flux:button size="sm" variant="{{ $viewTab === 'kanban' ? 'primary' : 'ghost' }}" wire:click="setTab('kanban')" icon="squares-plus">Kanban</flux:button>
        <flux:button size="sm" variant="{{ $viewTab === 'list' ? 'primary' : 'ghost' }}" wire:click="setTab('list')" icon="table-cells">Lista</flux:button>
        <flux:button size="sm" variant="{{ $viewTab === 'resources' ? 'primary' : 'ghost' }}" wire:click="setTab('resources')" icon="users">Recursos</flux:button>
        <flux:button size="sm" variant="{{ $viewTab === 'calendar' ? 'primary' : 'ghost' }}" wire:click="setTab('calendar')" icon="calendar-days">Calendario</flux:button>
    </div>

    @if ($viewTab === 'graficos')
        <section class="grid grid-cols-2 gap-2 lg:grid-cols-4">
            <x-mechanics.kpi-stat label="Abiertas" :value="number_format($kpis['open'])" :percent="$kpiPercents['open']" tone="cyan" />
            <x-mechanics.kpi-stat label="En proceso" :value="number_format($kpis['in_progress'])" :percent="$kpiPercents['in_progress']" tone="cyan" />
            <x-mechanics.kpi-stat label="Vencidas prog." :value="number_format($kpis['overdue'])" :percent="$kpiPercents['overdue']" tone="rose" />
            <x-mechanics.kpi-stat label="Cerradas / fin." :value="number_format($kpis['finished_closed'])" :percent="$kpiPercents['finished_closed']" tone="emerald" />
        </section>

        <div class="grid gap-2 sm:grid-cols-2">
            <x-mechanics.kpi-stat label="Total filtrado" :value="number_format($totalFiltered)" />
            <x-mechanics.kpi-stat label="Costo acumulado" :value="'S/ '.number_format((float) $kpis['total_cost_period'], 0)" tone="amber" />
        </div>

        <div class="grid gap-3 xl:grid-cols-2">
            <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <x-charts.chart id="wo-by-status" title="OT por estado" subtitle="Distribución según filtros activos." :config="$boardCharts['by_status']" height="260" />
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <x-charts.chart id="wo-by-type" title="OT por tipo" :config="$boardCharts['by_type']" height="260" />
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <x-charts.chart id="wo-by-priority" title="OT por prioridad" :config="$boardCharts['by_priority']" height="260" />
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <x-charts.chart id="wo-cost-mix" title="Composición de costos" subtitle="Mano de obra vs repuestos." :config="$boardCharts['cost_mix']" height="260" />
            </article>
        </div>

        @if ($topTechnicianLoads->isNotEmpty())
            <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <x-charts.chart id="wo-tech-load" title="Carga por técnico" subtitle="OT abiertas asignadas." :config="$boardCharts['technician_load']" height="280" />
            </article>
        @endif

        @if ($avgAttentionDays !== null)
            <p class="text-center text-[11px] text-slate-500">Promedio de atención (cerradas/finalizadas): <span class="font-semibold text-slate-700 dark:text-slate-200">{{ number_format($avgAttentionDays, 1) }} días</span></p>
        @endif
    @endif

    @if ($viewTab === 'kanban')
        @can('mantenimientos.crear')
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Arrastre las tarjetas entre columnas para cambiar el estado.</p>
        @else
            <flux:callout variant="warning" class="text-xs">Sin permiso para mover tarjetas.</flux:callout>
        @endcan

        <div class="flex gap-2 overflow-x-auto pb-2">
            @foreach ($kanbanStatuses as $column)
                <div class="flex w-64 shrink-0 flex-col rounded-xl border border-slate-200 bg-slate-50/70 dark:border-slate-800 dark:bg-slate-900/70">
                    <div class="flex items-center justify-between border-b border-slate-200 px-2 py-1 dark:border-slate-800">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $column->label() }}</p>
                        <span class="text-xs font-semibold tabular-nums text-slate-700 dark:text-slate-200">{{ ($kanbanGrouped[$column->value()] ?? collect())->count() }}</span>
                    </div>
                    <div
                        class="flex min-h-[10rem] flex-1 flex-col gap-1.5 p-1.5"
                        data-status="{{ $column->value() }}"
                        x-on:dragover.prevent
                        x-on:drop.prevent="if (draggingId) { $wire.kanbanMove(draggingId, '{{ $column->value() }}'); draggingId = null }"
                    >
                        @foreach ($kanbanGrouped[$column->value()] ?? [] as $card)
                            @php
                                $risk = $card->scheduleRiskLabel();
                                $riskColor = match ($risk) {
                                    'vencida' => 'rose',
                                    'hoy' => 'amber',
                                    'proxima' => 'cyan',
                                    default => null,
                                };
                                $ring = match ($card->priority) {
                                    'critica' => 'border-l-2 border-l-rose-500',
                                    'alta' => 'border-l-2 border-l-amber-500',
                                    'media' => 'border-l-2 border-l-sky-500',
                                    default => 'border-l-2 border-l-slate-200 dark:border-l-slate-700',
                                };
                            @endphp
                            <div
                                wire:key="kan-{{ $card->id }}"
                                draggable="{{ auth()->user()->can('mantenimientos.crear') ? 'true' : 'false' }}"
                                x-on:dragstart="draggingId = {{ $card->id }}"
                                x-on:dragend="draggingId = null"
                                class="{{ $ring }} cursor-grab rounded-lg border border-slate-200 bg-white p-2 text-[11px] shadow-sm active:cursor-grabbing dark:border-slate-700 dark:bg-slate-950"
                            >
                                <div class="flex items-start justify-between gap-1">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $card->code }}</span>
                                    @if ($riskColor)
                                        <flux:badge color="{{ $riskColor }}" size="sm">{{ $risk }}</flux:badge>
                                    @endif
                                </div>
                                <p class="mt-0.5 truncate text-slate-600 dark:text-slate-400">{{ $card->equipment?->internal_code }} · {{ $card->workProject?->code ?? '—' }}</p>
                                <p class="truncate text-[10px] text-slate-500">{{ $card->responsibleUser?->name ?? 'Sin técnico' }} · {{ $card->scheduled_date?->format('d/m/y') ?? '—' }}</p>
                                @can('mantenimientos.crear')
                                    <div class="mt-1.5 flex justify-end gap-0">
                                        <flux:tooltip content="Asignar">
                                            <flux:button variant="ghost" size="sm" icon="user-plus" wire:click="openAssignModal({{ $card->id }})" class="!size-6 !min-h-0 !p-0" aria-label="Asignar"></flux:button>
                                        </flux:tooltip>
                                        <flux:tooltip content="Editar">
                                            <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="openEdit({{ $card->id }})" class="!size-6 !min-h-0 !p-0" aria-label="Editar"></flux:button>
                                        </flux:tooltip>
                                    </div>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($viewTab === 'list' && $listRows !== null)
        <div class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-1.5">
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Orden</label>
                <select wire:model.live="sortColumn" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="issued_at">Emisión</option>
                    <option value="scheduled_date">Programación</option>
                    <option value="code">Código</option>
                    <option value="total_cost">Costo</option>
                    <option value="status">Estado</option>
                    <option value="priority">Prioridad</option>
                    <option value="type">Tipo</option>
                </select>
                <flux:button variant="ghost" size="sm" wire:click="sortBy('{{ $sortColumn }}')" type="button" icon="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" />
            </div>
            @can('mantenimientos.crear')
                @if ($selectedIds !== [])
                    <flux:badge size="sm">{{ count($selectedIds) }} sel.</flux:badge>
                    <select wire:model.live="bulkTargetStatus" class="h-8 min-w-[10rem] rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">Estado objetivo…</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value() }}">{{ $s->label() }}</option>
                        @endforeach
                    </select>
                    <flux:button variant="primary" size="sm" wire:click="bulkApplyStatus" icon="bolt">Aplicar</flux:button>
                @endif
            @endcan

            @can('mecanica.exportar')
                <flux:dropdown>
                    <flux:button variant="outline" icon="arrow-down-tray" size="sm">Exportar</flux:button>
                    <flux:menu>
                        <flux:menu.item icon="document-text" wire:click="openWorkOrdersReportPdf('detail')">PDF · Detalle</flux:menu.item>
                        <flux:menu.item icon="table-cells" href="{{ route('mechanics.report.work-orders.excel', $exportParams) }}">Excel · Detalle</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item icon="users" wire:click="openWorkOrdersReportPdf('by-technician')">PDF · Por técnico</flux:menu.item>
                        <flux:menu.item href="{{ route('mechanics.report.work-orders.by-technician.excel', $exportParams) }}">Excel · Por técnico</flux:menu.item>
                        <flux:menu.item icon="building-office" wire:click="openWorkOrdersReportPdf('by-project')">PDF · Por obra</flux:menu.item>
                        <flux:menu.item href="{{ route('mechanics.report.work-orders.by-project.excel', $exportParams) }}">Excel · Por obra</flux:menu.item>
                        <flux:menu.item icon="wrench-screwdriver" wire:click="openWorkOrdersReportPdf('by-equipment')">PDF · Por equipo</flux:menu.item>
                        <flux:menu.item href="{{ route('mechanics.report.work-orders.by-equipment.excel', $exportParams) }}">Excel · Por equipo</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item icon="exclamation-triangle" wire:click="openWorkOrdersReportPdf('overdue')">PDF · Vencidas</flux:menu.item>
                        <flux:menu.item href="{{ route('mechanics.report.work-orders.overdue.excel', $exportParams) }}">Excel · Vencidas</flux:menu.item>
                        <flux:menu.item icon="currency-dollar" wire:click="openWorkOrdersReportPdf('costs')">PDF · Costos</flux:menu.item>
                        <flux:menu.item href="{{ route('mechanics.report.work-orders.costs.excel', $exportParams) }}">Excel · Costos</flux:menu.item>
                        <flux:menu.item icon="chart-bar" wire:click="openWorkOrdersReportPdf('types')">PDF · Tipos</flux:menu.item>
                        <flux:menu.item href="{{ route('mechanics.report.work-orders.types.excel', $exportParams) }}">Excel · Tipos</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            @endcan
        </div>

        @php
            $listHeaders = auth()->user()->can('mantenimientos.crear')
                ? ['', 'OT', 'Equipo', 'Tipo', 'Estado', 'Prioridad', 'Técnico', 'Prog.', 'Emisión', 'Total', '']
                : ['OT', 'Equipo', 'Tipo', 'Estado', 'Prioridad', 'Técnico', 'Prog.', 'Emisión', 'Total', ''];
        @endphp

        <x-platform.compact-table dense :headers="$listHeaders">
            @forelse ($listRows as $row)
                @php $risk = $row->scheduleRiskLabel(); @endphp
                <tr wire:key="list-{{ $row->id }}" @class(['bg-rose-50/80 dark:bg-rose-950/20' => $risk === 'vencida'])>
                    @can('mantenimientos.crear')
                        <td class="!px-1.5 !py-1">
                            <input type="checkbox" value="{{ $row->id }}" wire:model.live="selectedIds" class="rounded" />
                        </td>
                    @endcan
                    <td class="whitespace-nowrap font-semibold text-slate-950 dark:text-white">{{ $row->code }}</td>
                    <td class="whitespace-nowrap">{{ $row->equipment?->internal_code }}</td>
                    <td class="whitespace-nowrap">
                        @foreach ($types as $t)
                            @if ($t->value() === $row->type) {{ $t->label() }} @endif
                        @endforeach
                    </td>
                    <td class="whitespace-nowrap">
                        @foreach ($statuses as $s)
                            @if ($s->value() === $row->status)
                                <x-platform.status-badge :value="$row->status" size="xs" />
                            @endif
                        @endforeach
                    </td>
                    <td class="whitespace-nowrap">
                        @foreach ($priorities as $p)
                            @if ($p->value() === $row->priority)
                                <x-platform.status-badge :value="$row->priority" size="xs" />
                            @endif
                        @endforeach
                        @if ($risk)
                            <flux:badge size="sm" color="{{ $risk === 'vencida' ? 'danger' : ($risk === 'hoy' ? 'amber' : 'cyan') }}">{{ $risk }}</flux:badge>
                        @endif
                    </td>
                    <td class="max-w-[8rem] truncate">{{ $row->responsibleUser?->name ?? '—' }}</td>
                    <td class="whitespace-nowrap tabular-nums">{{ $row->scheduled_date?->format('d/m/y') ?? '—' }}</td>
                    <td class="whitespace-nowrap tabular-nums">{{ $row->issued_at?->format('d/m/y') ?? '—' }}</td>
                    <td class="whitespace-nowrap text-end tabular-nums">{{ number_format((float) $row->total_cost, 2) }}</td>
                    <td class="!px-1 !py-1">
                        <div class="flex items-center justify-end gap-0">
                            @can('mantenimientos.crear')
                                <flux:tooltip content="Asignar">
                                    <flux:button variant="ghost" size="sm" icon="user-plus" wire:click="openAssignModal({{ $row->id }})" class="!size-6 !min-h-0 !p-0" aria-label="Asignar"></flux:button>
                                </flux:tooltip>
                                <flux:tooltip content="Editar">
                                    <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="openEdit({{ $row->id }})" class="!size-6 !min-h-0 !p-0" aria-label="Editar"></flux:button>
                                </flux:tooltip>
                            @endcan
                            @can('mantenimientos.cerrar')
                                @if (! in_array($row->status, [\App\Enums\FleetWorkOrderStatus::Closed->value(), \App\Enums\FleetWorkOrderStatus::Cancelled->value()], true))
                                    <flux:tooltip content="Cerrar">
                                        <flux:button variant="ghost" size="sm" icon="check-circle" wire:click="closeOrder({{ $row->id }})" class="!size-6 !min-h-0 !p-0 !text-emerald-600" aria-label="Cerrar"></flux:button>
                                    </flux:tooltip>
                                @endif
                            @endcan
                            @can('mecanica.eliminar')
                                <flux:tooltip content="Eliminar">
                                    <flux:button variant="ghost" size="sm" icon="trash" wire:click="deleteRow({{ $row->id }})" wire:confirm="¿Eliminar OT?" class="!size-6 !min-h-0 !p-0 !text-rose-600" aria-label="Eliminar"></flux:button>
                                </flux:tooltip>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ auth()->user()->can('mantenimientos.crear') ? 11 : 10 }}" class="!py-5 text-center text-[11px] text-slate-500">Sin registros para los filtros.</td>
                </tr>
            @endforelse
        </x-platform.compact-table>

        <x-mechanics.pagination>{{ $listRows->links() }}</x-mechanics.pagination>
    @endif

    @if ($viewTab === 'resources')
        <div class="grid gap-3 lg:grid-cols-2">
            @foreach ($technicianStats as $bucket)
                <div wire:key="res-{{ $bucket['user']->id }}" class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ $bucket['user']->name }}</p>
                        <div class="flex gap-1.5 text-[10px] text-slate-500">
                            <span>{{ $bucket['open'] }} abiertas</span>
                            <span>·</span>
                            <span class="text-rose-600">{{ $bucket['overdue'] }} venc.</span>
                        </div>
                    </div>
                    <div class="mt-2 divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($bucket['pending'] as $p)
                            <div class="flex items-center justify-between gap-2 py-1.5 text-[11px]" wire:key="pend-{{ $p->id }}">
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ $p->code }}</span>
                                <span class="truncate text-slate-500">{{ $p->equipment?->internal_code ?? '—' }} · {{ $p->scheduled_date?->format('d/m/y') ?? '—' }}</span>
                                @can('mantenimientos.crear')
                                    <div class="flex shrink-0 gap-0">
                                        <flux:button size="sm" variant="ghost" icon="user-plus" wire:click="openAssignModal({{ $p->id }})" class="!size-6 !min-h-0 !p-0" aria-label="Asignar"></flux:button>
                                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="openEdit({{ $p->id }})" class="!size-6 !min-h-0 !p-0" aria-label="Editar"></flux:button>
                                    </div>
                                @endcan
                            </div>
                        @empty
                            <p class="py-4 text-center text-[11px] text-slate-500">Sin pendientes.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
        @if ($technicianStats->isEmpty())
            <flux:callout variant="warning" class="text-xs">Sin técnicos vinculados a la empresa activa.</flux:callout>
        @endif
    @endif

    @if ($viewTab === 'calendar')
        <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-2 py-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2">
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Mes</label>
                <input type="month" wire:model.live="calendarMonth" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            @can('mantenimientos.crear')
                <p class="text-[10px] text-slate-500">“+ día” crea OT con esa fecha programada.</p>
            @endcan
        </div>

        <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="grid grid-cols-7 border-b border-slate-200 text-center text-[10px] font-semibold uppercase text-slate-500 dark:border-slate-800">
                @foreach (['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $d)
                    <div class="px-1 py-1.5">{{ $d }}</div>
                @endforeach
            </div>
            <div class="grid grid-cols-7 auto-rows-fr gap-px bg-slate-100 p-px dark:bg-slate-800">
                @php
                    $dowIso = $calendarStart->copy()->startOfMonth()->dayOfWeekIso;
                    $pad = $dowIso === 7 ? 6 : ($dowIso - 1);
                @endphp
                @foreach (range(1, $pad) as $_)
                    <div class="min-h-[5rem] bg-slate-50 dark:bg-slate-950"></div>
                @endforeach
                @foreach (range(1, $calendarEnd->day) as $dom)
                    @php
                        $thatDay = $calendarStart->copy()->day((int) $dom);
                        $dStr = $thatDay->toDateString();
                        $dayOrders = $calendarWorkOrders->filter(fn ($o) => $o->scheduled_date?->toDateString() === $dStr)->values();
                        $prev = $calendarPreventive->filter(fn ($o) => $o->scheduled_date?->toDateString() === $dStr)->values();
                        $inspDay = $calendarInspections->filter(fn ($o) => $o->due_at?->toDateString() === $dStr)->values();
                    @endphp
                    <div
                        wire:key="cal-{{ $dStr }}"
                        class="relative min-h-[5rem] space-y-0.5 bg-white p-1 text-left text-[10px] dark:bg-slate-900 {{ $thatDay->isToday() ? 'ring-1 ring-cyan-500/50' : '' }}"
                    >
                        <div class="flex items-start justify-between">
                            <span class="inline-flex size-5 items-center justify-center rounded-full text-[11px] font-semibold {{ $thatDay->isToday() ? 'bg-cyan-600 text-white' : 'text-slate-700 dark:text-white' }}">{{ $dom }}</span>
                            @can('mantenimientos.crear')
                                <button type="button" wire:click="startCreateFromCalendar('{{ $dStr }}')" class="text-cyan-600 hover:underline">+</button>
                            @endcan
                        </div>
                        @foreach ($dayOrders as $wo)
                            <div wire:key="cal-w-{{ $wo->id }}" class="truncate rounded border px-0.5 py-px {{ $wo->scheduleRiskLabel() === 'vencida' ? 'border-rose-400 bg-rose-50 dark:bg-rose-950/40' : 'border-slate-200 dark:border-slate-700' }}">
                                <button type="button" wire:click="openEdit({{ $wo->id }})" class="block w-full truncate text-left font-semibold text-slate-800 hover:text-cyan-700 dark:text-slate-100">{{ $wo->code }}</button>
                            </div>
                        @endforeach
                        @foreach ($prev as $m)
                            <div wire:key="cal-prev-{{ $m->id }}" class="truncate rounded border border-amber-200 bg-amber-50 px-0.5 dark:border-amber-700 dark:bg-amber-950/30">PRV #{{ $m->id }}</div>
                        @endforeach
                        @foreach ($inspDay as $rev)
                            <div wire:key="cal-ins-{{ $rev->id }}" class="truncate rounded border border-purple-200 bg-purple-50 px-0.5 dark:border-purple-800 dark:bg-purple-950/30">Rev. {{ $rev->equipment?->internal_code ?? '—' }}</div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <x-platform.modal compact :show="$showAssignModal" max-width="max-w-sm">
        <div class="flex items-center justify-between gap-2">
            <h2 class="text-base font-semibold text-slate-950 dark:text-white">Asignar técnico</h2>
            <flux:button variant="ghost" size="sm" wire:click="closeAssignModal" type="button">Cerrar</flux:button>
        </div>
        <div class="mt-3">
            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Responsable</label>
            <select wire:model.live="assignResponsibleUserId" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Sin asignar</option>
                @foreach ($responsibleUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mt-3 flex justify-end gap-2 border-t border-slate-200 pt-3 dark:border-slate-800">
            <flux:button variant="outline" size="sm" wire:click="closeAssignModal" type="button">Cancelar</flux:button>
            <flux:button variant="primary" size="sm" wire:click="saveAssignment">Guardar</flux:button>
        </div>
    </x-platform.modal>

    @include('livewire.mechanics.partials.fleet-work-order-form-modal')

    <x-platform.pdf-viewer-modal
        :show="$showPdfModal"
        :url="$pdfViewerUrl"
        :title="$pdfViewerTitle"
        :subtitle="$pdfViewerSubtitle"
        :allowExternalOpen="false"
    />
</div>
