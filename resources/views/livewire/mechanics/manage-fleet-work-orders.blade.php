<div class="space-y-6" x-data="{ draggingId: null }">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-cyan-700 dark:text-cyan-400">Mecánica</p>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ $title }}</h1>
            <flux:text size="sm" class="mt-1 text-slate-600 dark:text-slate-400">Tablero de OT: kanban, carga técnico, tabla operativa y calendario.</flux:text>
        </div>
        <div class="flex flex-wrap gap-2">
            <a wire:navigate href="{{ route('modules.mechanics') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium dark:border-slate-600">Dashboard</a>
            @can('mantenimientos.crear')
                <flux:button variant="primary" icon="plus" wire:click="openCreate" size="sm">Nueva OT</flux:button>
            @endcan
        </div>
    </div>

    {{-- KPIs --}}
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-7">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-medium uppercase text-slate-500">Abiertas</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($kpis['open']) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-medium uppercase text-slate-500">En proceso</p>
            <p class="mt-1 text-2xl font-bold text-cyan-700 dark:text-cyan-300">{{ number_format($kpis['in_progress']) }}</p>
        </div>
        <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4 shadow-sm dark:border-rose-900/40 dark:bg-rose-950/30">
            <p class="text-xs font-medium uppercase text-rose-700 dark:text-rose-300">Vencidas prog.</p>
            <p class="mt-1 text-2xl font-bold text-rose-700 dark:text-rose-200">{{ number_format($kpis['overdue']) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-medium uppercase text-slate-500">Finaliz./cerradas</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($kpis['finished_closed']) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:col-span-2 xl:col-span-1">
            <p class="text-xs font-medium uppercase text-slate-500">Costo total (filtro)</p>
            <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">S/&nbsp;{{ number_format((float) $kpis['total_cost_period'], 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:col-span-2 xl:col-span-2">
            <p class="text-xs font-medium uppercase text-slate-500">Promedio atención (cerr./fin.)</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                {{ $avgAttentionDays !== null ? number_format($avgAttentionDays, 1).' d' : '—' }}
            </p>
        </div>
    </section>

    @if ($topTechnicianLoads->isNotEmpty())
        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Mayor carga (OT abiertas)</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($topTechnicianLoads as $slot)
                    <flux:badge color="zinc">{{ $slot['user']->name }}: {{ $slot['open'] }} abiertas</flux:badge>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Filtros compartidos --}}
    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap items-end gap-3">
            <flux:field class="min-w-[10rem] flex-1">
                <flux:label>Búsqueda</flux:label>
                <flux:input wire:model.live.debounce.400ms="search" placeholder="Código OT, equipo…" />
            </flux:field>
            <flux:field>
                <flux:label>Obra</flux:label>
                <select wire:model.live="filter_project_id" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <option value="">Todas</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->code }}</option>
                    @endforeach
                </select>
            </flux:field>
            <flux:field>
                <flux:label>Equipo</flux:label>
                <select wire:model.live="filter_equipment_id" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <option value="">Todos</option>
                    @foreach ($equipments as $equipment)
                        <option value="{{ $equipment->id }}">{{ $equipment->internal_code }}</option>
                    @endforeach
                </select>
            </flux:field>
            <flux:field>
                <flux:label>Técnico</flux:label>
                <select wire:model.live="filter_technician_id" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <option value="">Todos</option>
                    @foreach ($responsibleUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </flux:field>
            <flux:field>
                <flux:label>Tipo OT</flux:label>
                <select wire:model.live="filter_type" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <option value="">Todos</option>
                    @foreach ($types as $t)
                        <option value="{{ $t->value() }}">{{ $t->label() }}</option>
                    @endforeach
                </select>
            </flux:field>
            <flux:field>
                <flux:label>Estado</flux:label>
                <select wire:model.live="filter_status" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <option value="">Todos</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value() }}">{{ $s->label() }}</option>
                    @endforeach
                </select>
            </flux:field>
            <flux:field>
                <flux:label>Prioridad</flux:label>
                <select wire:model.live="filter_priority" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <option value="">Todas</option>
                    @foreach ($priorities as $p)
                        <option value="{{ $p->value() }}">{{ $p->label() }}</option>
                    @endforeach
                </select>
            </flux:field>
            <flux:field>
                <flux:label>Prog. desde</flux:label>
                <flux:input type="date" wire:model.live="filter_scheduled_from" />
            </flux:field>
            <flux:field>
                <flux:label>Prog. hasta</flux:label>
                <flux:input type="date" wire:model.live="filter_scheduled_to" />
            </flux:field>
            <flux:field>
                <flux:label>Cierre desde</flux:label>
                <flux:input type="date" wire:model.live="filter_closed_from" />
            </flux:field>
            <flux:field>
                <flux:label>Cierre hasta</flux:label>
                <flux:input type="date" wire:model.live="filter_closed_to" />
            </flux:field>
            <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <flux:checkbox wire:model.live="filter_overdue_only" />
                Solo vencidas (programación)
            </label>
            <flux:button variant="ghost" size="sm" wire:click="resetFilters" type="button">Limpiar filtros</flux:button>
        </div>
    </div>

    {{-- Pestañas --}}
    <div class="flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-slate-50/80 p-2 dark:border-slate-800 dark:bg-slate-950/50">
        <flux:button size="sm" variant="{{ $viewTab === 'kanban' ? 'primary' : 'ghost' }}" wire:click="setTab('kanban')" icon="squares-plus">Kanban</flux:button>
        <flux:button size="sm" variant="{{ $viewTab === 'list' ? 'primary' : 'ghost' }}" wire:click="setTab('list')" icon="table-cells">Lista</flux:button>
        <flux:button size="sm" variant="{{ $viewTab === 'resources' ? 'primary' : 'ghost' }}" wire:click="setTab('resources')" icon="users">Recursos</flux:button>
        <flux:button size="sm" variant="{{ $viewTab === 'calendar' ? 'primary' : 'ghost' }}" wire:click="setTab('calendar')" icon="calendar-days">Calendario</flux:button>
    </div>

    @if ($viewTab === 'kanban')
        @can('mantenimientos.crear')
            <flux:callout variant="neutral" icon="information-circle" class="text-sm">
                Arrastre las tarjetas entre columnas para cambiar el estado.
            </flux:callout>
        @else
            <flux:callout variant="warning" class="text-sm">No tiene permiso para mover tarjetas en el tablero.</flux:callout>
        @endcan

        <div class="flex gap-3 overflow-x-auto pb-4">
            @foreach ($kanbanStatuses as $column)
                <div class="flex w-72 shrink-0 flex-col rounded-3xl border border-slate-200 bg-slate-50/70 dark:border-slate-800 dark:bg-slate-900/70">
                    <div class="border-b border-slate-200 px-2 py-1 dark:border-slate-800">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $column->label() }}</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ $kanbanGrouped[$column->value()]->count() }}</p>
                    </div>
                    <div
                        class="scrollbar-thin flex min-h-[12rem] flex-1 flex-col gap-2 p-2"
                        data-status="{{ $column->value() }}"
                        x-on:dragover.prevent
                        x-on:drop.prevent="if (draggingId) { $wire.kanbanMove(draggingId, '{{ $column->value() }}'); draggingId = null }"
                        @if (! auth()->user()->can('mantenimientos.crear')) data-readonly @endif
                    >
                        @foreach ($kanbanGrouped[$column->value()] as $card)
                            @php
                                $risk = $card->scheduleRiskLabel();
                                $riskColor = match ($risk) {
                                    'vencida' => 'rose',
                                    'hoy' => 'amber',
                                    'proxima' => 'cyan',
                                    default => null,
                                };
                            @endphp
                            @php
                                $ring = match ($card->priority) {
                                    'critica' => 'border-l-4 border-l-rose-500',
                                    'alta' => 'border-l-4 border-l-amber-500',
                                    'media' => 'border-l-4 border-l-sky-500',
                                    default => 'border-l-4 border-l-slate-200 dark:border-l-slate-700',
                                };
                            @endphp
                            <div
                                wire:key="kan-{{ $card->id }}"
                                draggable="{{ auth()->user()->can('mantenimientos.crear') ? 'true' : 'false' }}"
                                @if (! auth()->user()->can('mantenimientos.crear')) title="Sin permiso para arrastrar" @endif
                                x-on:dragstart="draggingId = {{ $card->id }}"
                                x-on:dragend="draggingId = null"
                                class="{{ $ring }} cursor-grab rounded-2xl border border-slate-200 bg-white p-3 shadow-sm active:cursor-grabbing dark:border-slate-700 dark:bg-slate-950"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $card->code }}</span>
                                    @if ($riskColor)
                                        <flux:badge color="{{ $riskColor }}" size="sm">
                                            {{ $risk }}
                                        </flux:badge>
                                    @endif
                                </div>
                                <flux:text size="xs" class="mt-1 text-slate-600">{{ $card->equipment?->internal_code }} · {{ $card->equipment?->name }}</flux:text>
                                <flux:text size="xs">{{ $card->workProject?->code ?? 'Sin obra' }}</flux:text>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach ($types as $t)
                                        @if ($t->value() === $card->type)
                                            <flux:badge size="sm" color="zinc">{{ $t->label() }}</flux:badge>
                                        @endif
                                    @endforeach
                                    @foreach ($priorities as $p)
                                        @if ($p->value() === $card->priority)
                                            <flux:badge size="sm" color="{{ $card->priority === 'critica' ? 'red' : ($card->priority === 'alta' ? 'orange' : 'zinc') }}">{{ $p->label() }}</flux:badge>
                                        @endif
                                    @endforeach
                                </div>
                                <flux:text size="xs" class="mt-1">{{ $card->responsibleUser?->name ?? 'Sin técnico' }}</flux:text>
                                <flux:text size="xs" class="mt-1 text-slate-500">{{ $card->scheduled_date?->format('d/m/Y') ?? 'Sin fecha' }}</flux:text>
                                @can('mantenimientos.crear')
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        <flux:button variant="ghost" size="xs" wire:click="openAssignModal({{ $card->id }})">Asignar</flux:button>
                                        <flux:button variant="ghost" size="xs" wire:click="openEdit({{ $card->id }})">Editar</flux:button>
                                    </div>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($viewTab === 'list')
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-3xl border border-slate-200 bg-white px-2.5 py-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center gap-2">
                @can('mantenimientos.crear')
                    @if ($selectedIds !== [])
                        <flux:badge>{{ count($selectedIds) }} sel.</flux:badge>
                        <select wire:model.live="bulkTargetStatus" class="min-w-[11rem] rounded-lg border border-slate-200 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950">
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
                        <flux:button variant="filled" icon="arrow-down-tray" size="sm">Exportar (filtros actu.)</flux:button>
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
                            <flux:menu.item icon="chart-bar" wire:click="openWorkOrdersReportPdf('types')">PDF · Tipos (prevent./corr.)</flux:menu.item>
                            <flux:menu.item href="{{ route('mechanics.report.work-orders.types.excel', $exportParams) }}">Excel · Tipos</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                @endcan
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs dark:divide-slate-800">
                    <thead class="bg-slate-50 text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:bg-slate-950">
                        <tr>
                            @can('mantenimientos.crear')
                                <th class="px-2.5 py-1.5"><input type="checkbox" wire:click="toggleSelectAllOnPage" class="rounded" /></th>
                            @endcan
                            <th class="cursor-pointer whitespace-nowrap px-2.5 py-1.5" wire:click="sortBy('code')">
                                OT {!! $sortColumn === 'code' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                            </th>
                            <th class="px-2.5 py-1.5">Equipo</th>
                            <th class="cursor-pointer px-2.5 py-1.5" wire:click="sortBy('type')">Tipo</th>
                            <th class="cursor-pointer px-2.5 py-1.5" wire:click="sortBy('status')">Estado</th>
                            <th class="cursor-pointer px-2.5 py-1.5" wire:click="sortBy('priority')">Prioridad</th>
                            <th class="px-2.5 py-1.5">Técnico</th>
                            <th class="cursor-pointer whitespace-nowrap px-2.5 py-1.5" wire:click="sortBy('scheduled_date')">Prog.</th>
                            <th class="cursor-pointer whitespace-nowrap px-2.5 py-1.5" wire:click="sortBy('issued_at')">Emisión</th>
                            <th class="cursor-pointer px-2.5 py-1.5 text-end" wire:click="sortBy('total_cost')">Total S/</th>
                            <th class="px-2.5 py-1.5 text-end"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($listRows as $row)
                            @php
                                $risk = $row->scheduleRiskLabel();
                            @endphp
                            <tr wire:key="list-{{ $row->id }}" @class([
                                'bg-rose-50/80 dark:bg-rose-950/20' => $risk === 'vencida',
                            ])>
                                @can('mantenimientos.crear')
                                    <td class="px-2 py-1">
                                        <input type="checkbox" value="{{ $row->id }}" wire:model.live="selectedIds" class="rounded" />
                                    </td>
                                @endcan
                                <td class="px-2 py-1 font-semibold">{{ $row->code }}</td>
                                <td class="px-2 py-1">{{ $row->equipment?->internal_code }}</td>
                                <td class="px-2 py-1">
                                    @foreach ($types as $t)
                                        @if ($t->value() === $row->type) {{ $t->label() }} @endif
                                    @endforeach
                                </td>
                                <td class="px-2 py-1">
                                    @foreach ($statuses as $s)
                                        @if ($s->value() === $row->status)
                                            <flux:badge color="blue" size="sm">{{ $s->label() }}</flux:badge>
                                        @endif
                                    @endforeach
                                </td>
                                <td class="px-2 py-1">
                                    @foreach ($priorities as $p)
                                        @if ($p->value() === $row->priority)
                                            <flux:badge color="{{ $row->priority === 'critica' ? 'danger' : ($row->priority === 'alta' ? 'orange' : 'zinc') }}" size="sm">{{ $p->label() }}</flux:badge>
                                        @endif
                                    @endforeach
                                    @if ($risk)
                                        <flux:badge size="sm" color="{{ $risk === 'vencida' ? 'danger' : ($risk === 'hoy' ? 'amber' : 'cyan') }}">{{ $risk }}</flux:badge>
                                    @endif
                                </td>
                                <td class="px-2 py-1">{{ $row->responsibleUser?->name ?? '—' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">{{ $row->scheduled_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">{{ $row->issued_at?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-2 py-1 text-end">{{ number_format((float) $row->total_cost, 2) }}</td>
                                <td class="px-2 py-1 text-end space-x-2">
                                    @can('mantenimientos.crear')
                                        <flux:button variant="ghost" size="xs" wire:click="openAssignModal({{ $row->id }})">Asignar</flux:button>
                                        <flux:button variant="ghost" size="xs" wire:click="openEdit({{ $row->id }})">Editar</flux:button>
                                    @endcan
                                    @can('mantenimientos.cerrar')
                                        @if (! in_array($row->status, [\App\Enums\FleetWorkOrderStatus::Closed->value(), \App\Enums\FleetWorkOrderStatus::Cancelled->value()], true))
                                            <flux:button variant="ghost" size="xs" wire:click="closeOrder({{ $row->id }})">Cerrar</flux:button>
                                        @endif
                                    @endcan
                                    @can('mecanica.eliminar')
                                        <flux:button variant="danger" size="xs" wire:click="deleteRow({{ $row->id }})" wire:confirm="¿Eliminar OT?">Eliminar</flux:button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ auth()->user()->can('mantenimientos.crear') ? 11 : 10 }}" class="px-4 py-8 text-center text-slate-500">Sin registros para los filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-2.5 py-1.5 dark:border-slate-800">
                {{ $listRows->links() }}
            </div>
        </div>
    @endif

    @if ($viewTab === 'resources')
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($technicianStats as $bucket)
                <div wire:key="res-{{ $bucket['user']->id }}" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-lg font-semibold dark:text-white">{{ $bucket['user']->name }}</p>
                            <p class="text-sm text-slate-500">{{ $bucket['open'] }} abiertas · {{ $bucket['overdue'] }} vencidas (prog.)</p>
                        </div>
                    </div>
                    <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($bucket['pending'] as $p)
                            <div class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm" wire:key="pend-{{ $p->id }}">
                                <span class="font-medium">{{ $p->code }}</span>
                                <span class="text-slate-600">{{ $p->equipment?->internal_code ?? '—' }} · {{ $p->scheduled_date?->format('d/m/Y') ?? '—' }}</span>
                                @can('mantenimientos.crear')
                                    <flux:button size="xs" variant="ghost" wire:click="openAssignModal({{ $p->id }})">Asignar / reasignar</flux:button>
                                    <flux:button size="xs" variant="ghost" wire:click="openEdit({{ $p->id }})">Reprogramar</flux:button>
                                @endcan
                            </div>
                        @empty
                            <p class="py-6 text-center text-slate-500">Sin pendientes para este técnico con los filtros actuales.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
        @if ($technicianStats->isEmpty())
            <flux:callout variant="warning">Sin técnicos vinculados a la empresa activa.</flux:callout>
        @endif
    @endif

    @if ($viewTab === 'calendar')
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-3xl border border-slate-200 bg-white px-2.5 py-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <flux:field class="flex items-end gap-2">
                <flux:label class="mr-2">Mes</flux:label>
                <flux:input type="month" wire:model.live="calendarMonth" />
            </flux:field>
            @can('mantenimientos.crear')
                <flux:text size="sm" class="max-w-xl text-slate-600 dark:text-slate-400">
                    Use “+ día” para crear OT con esa fecha programada. En los eventos, “Abrir” edita reprogramaciones.
                </flux:text>
            @endcan
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="grid grid-cols-7 border-b border-slate-200 text-center text-xs font-semibold uppercase text-slate-500 dark:border-slate-800 dark:text-slate-400">
                @foreach (['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $d)
                    <div class="px-1 py-2">{{ $d }}</div>
                @endforeach
            </div>
            <div class="grid grid-cols-7 auto-rows-fr gap-px bg-slate-100 p-px dark:bg-slate-800">
                @php
                    $dowIso = $calendarStart->copy()->startOfMonth()->dayOfWeekIso;
                    $pad = $dowIso === 7 ? 6 : ($dowIso - 1);
                @endphp
                @foreach (range(1, $pad) as $_)
                    <div class="min-h-[6rem] bg-slate-50 dark:bg-slate-950"></div>
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
                        class="relative min-h-[6rem] space-y-1 bg-white p-1 text-left text-xs shadow-sm dark:bg-slate-900 {{ $thatDay->isToday() ? 'ring-2 ring-cyan-500/40' : '' }}"
                    >
                        <div class="flex items-start justify-between">
                            <span class="inline-flex size-7 items-center justify-center rounded-full text-sm font-semibold {{ $thatDay->isToday() ? 'bg-cyan-600 text-white' : 'text-slate-700 dark:text-white' }}">
                                {{ $dom }}
                            </span>
                            @can('mantenimientos.crear')
                                <button type="button" wire:click="startCreateFromCalendar('{{ $dStr }}')" class="text-cyan-600 hover:underline">+ día</button>
                            @endcan
                        </div>
                        @foreach ($dayOrders as $wo)
                            <div wire:key="cal-w-{{ $wo->id }}" class="truncate rounded-lg border px-1 py-0.5 {{ $wo->scheduleRiskLabel() === 'vencida' ? 'border-rose-400 bg-rose-50 dark:bg-rose-950/40' : 'border-slate-200 dark:border-slate-700' }}">
                                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $wo->code }}</span>
                                ·
                                @if ($wo->type === \App\Enums\FleetWorkOrderType::Preventive->value())
                                    Prev.
                                @elseif ($wo->type === \App\Enums\FleetWorkOrderType::Corrective->value())
                                    Corr.
                                @elseif ($wo->type === \App\Enums\FleetWorkOrderType::Inspection->value())
                                    Insp.
                                @elseif ($wo->type === \App\Enums\FleetWorkOrderType::TechnicalInspection->value())
                                    Rev.t.
                                @else
                                    {{ $wo->type }}
                                @endif
                                @can('mantenimientos.crear')
                                    <button type="button" wire:click="openEdit({{ $wo->id }})" class="block w-full truncate text-left text-[10px] text-cyan-700 hover:underline">Abrir OT</button>
                                @endcan
                            </div>
                        @endforeach
                        @foreach ($prev as $m)
                            <div wire:key="cal-prev-{{ $m->id }}" class="truncate rounded-lg border border-amber-200 bg-amber-50 px-1 dark:border-amber-700 dark:bg-amber-950/30">
                                PRV #{{ $m->id }} · {{ $m->equipment?->internal_code ?? '—' }}
                            </div>
                        @endforeach
                        @foreach ($inspDay as $rev)
                            <div wire:key="cal-ins-{{ $rev->id }}" class="truncate rounded-lg border border-purple-200 bg-purple-50 px-1 dark:border-purple-800 dark:bg-purple-950/30">
                                Rev. eq. {{ $rev->equipment?->internal_code ?? '—' }}
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <flux:modal wire:model.self="showAssignModal" class="max-w-lg">
        <flux:heading size="lg">Asignar técnico</flux:heading>
        <flux:field class="mt-4">
            <flux:label>Responsable</flux:label>
            <select wire:model.live="assignResponsibleUserId" class="mt-1 w-full rounded-lg border border-slate-200 px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950">
                <option value="">Sin asignar</option>
                @foreach ($responsibleUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </flux:field>
        <div class="mt-6 flex justify-end gap-2">
            <flux:button variant="ghost" wire:click="closeAssignModal">Cancelar</flux:button>
            <flux:button variant="primary" wire:click="saveAssignment">Guardar</flux:button>
        </div>
    </flux:modal>

    @include('livewire.mechanics.partials.fleet-work-order-form-modal')

    <x-platform.pdf-viewer-modal
        :show="$showPdfModal"
        :url="$pdfViewerUrl"
        :title="$pdfViewerTitle"
        :subtitle="$pdfViewerSubtitle"
        :allowExternalOpen="false"
    />
</div>
