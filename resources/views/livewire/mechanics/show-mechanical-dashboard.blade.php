<div class="space-y-8">
    @if ($alerts !== [])
        <div class="space-y-2">
            @foreach ($alerts as $alert)
                <div
                    class="rounded-2xl border px-4 py-3 text-sm
                        @class([
                            'border-rose-300 bg-rose-50 text-rose-900 dark:border-rose-800 dark:bg-rose-950/60 dark:text-rose-50' => ($alert['type'] ?? '') === 'danger',
                            'border-amber-300 bg-amber-50 text-amber-950 dark:border-amber-800 dark:bg-amber-950/50 dark:text-amber-50' => ($alert['type'] ?? '') !== 'danger',
                        ])"
                >
                    {{ $alert['message'] }}
                </div>
            @endforeach
        </div>
    @endif

    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Mecanica y maquinaria</h1>
            <p class="mt-1 max-w-3xl text-sm text-slate-600 dark:text-slate-300">
                Indicadores por empresa activa: equipos, revisiones, mantenimiento, OT y costos. Usa el menu lateral para registrar detalle.
            </p>
        </div>
        <nav class="flex flex-wrap gap-2 text-sm">
            @foreach ([
                ['Equipos', 'mechanics.equipments'],
                ['Revisiones', 'mechanics.inspections'],
                ['Preventivo', 'mechanics.preventive'],
                ['Correctivo', 'mechanics.corrective'],
                ['Ordenes de trabajo', 'mechanics.work-orders'],
                ['Repuestos', 'mechanics.spare-parts'],
            ] as [$label, $routeName])
                <a
                    wire:navigate
                    href="{{ route($routeName) }}"
                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 font-medium text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
                >{{ $label }}</a>
            @endforeach
        </nav>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Total equipos</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($kpis['total_equipment']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Operativos / en mantenimiento / averiados</p>
            <p class="mt-2 text-xl font-semibold text-slate-950 dark:text-white">
                {{ number_format($kpis['operational']) }} · {{ number_format($kpis['in_maintenance']) }} · {{ number_format($kpis['broken']) }}
            </p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Revision vencida / por vencer</p>
            <p class="mt-2 text-xl font-semibold text-slate-950 dark:text-white">
                {{ number_format($kpis['technical_expired']) }} · {{ number_format($kpis['technical_due_soon']) }}
            </p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">OT abiertas · costo mant. acum.</p>
            <p class="mt-2 text-xl font-semibold text-slate-950 dark:text-white">
                {{ number_format($kpis['work_orders_open']) }} · S/ {{ number_format($kpis['maintenance_cost_total'], 2) }}
            </p>
        </div>
    </section>

    @if (auth()->user()->can('mecanica.exportar') || auth()->user()->can('revisiones.exportar'))
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Reportes PDF / Excel</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Alcance multiempresa segun empresa activa. Para revisiones tecnicas aplica revisiones.exportar; los demas usan mecanica.exportar.</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Equipos', 'mechanics.report.equipments.pdf', 'mechanics.report.equipments.excel', 'mecanica.exportar'],
                    ['Estado maquinaria', 'mechanics.report.machinery-status.pdf', 'mechanics.report.machinery-status.excel', 'mecanica.exportar'],
                    ['Revisiones tecnicas', 'mechanics.report.inspections.pdf', 'mechanics.report.inspections.excel', 'revisiones.exportar'],
                    ['Mantenimiento preventivo', 'mechanics.report.preventive.pdf', 'mechanics.report.preventive.excel', 'mecanica.exportar'],
                    ['Mantenimiento correctivo', 'mechanics.report.corrective.pdf', 'mechanics.report.corrective.excel', 'mecanica.exportar'],
                    ['Ordenes de trabajo', 'mechanics.report.work-orders.pdf', 'mechanics.report.work-orders.excel', 'mecanica.exportar'],
                    ['Costos de mantenimiento', 'mechanics.report.maintenance-costs.pdf', 'mechanics.report.maintenance-costs.excel', 'mecanica.exportar'],
                    ['Repuestos consumidos', 'mechanics.report.consumed-spares.pdf', 'mechanics.report.consumed-spares.excel', 'mecanica.exportar'],
                    ['Equipos por obra', 'mechanics.report.equipment-by-project.pdf', 'mechanics.report.equipment-by-project.excel', 'mecanica.exportar'],
                ] as [$label, $routePdf, $routeXlsx, $perm])
                    @can($perm)
                        <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-slate-50/80 px-3 py-3 dark:border-slate-800 dark:bg-slate-950/50">
                            <span class="min-w-0 flex-1 text-sm font-medium text-slate-800 dark:text-slate-100">{{ $label }}</span>
                            <a href="{{ route($routePdf) }}" target="_blank" rel="noopener" class="rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-semibold text-white dark:bg-cyan-600 dark:text-slate-950">PDF</a>
                            <a href="{{ route($routeXlsx) }}" class="rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:border-slate-600 dark:text-slate-200">Excel</a>
                        </div>
                    @endcan
                @endforeach
            </div>
        </section>
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        <x-charts.chart id="mech-equipment-status" title="Equipos por estado" :config="$charts['equipment_by_status']" />
        <x-charts.chart id="mech-tech" title="Revisiones vencidas vs vigentes" :config="$charts['technical_expired_vs_valid']" />
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <x-charts.chart id="mech-cost-month" title="Costos por mes" subtitle="OT cerradas y mantenimientos sin OT" :config="$charts['cost_by_month']" height="320" />
        <x-charts.chart id="mech-prev-corr" title="Preventivo vs correctivo" :config="$charts['maintenance_preventivo_vs_correctivo']" height="320" />
    </div>

    <x-charts.chart id="mech-cost-eq" title="Top equipos por costo (OT cerradas)" :config="$charts['cost_by_equipment']" height="360" />
</div>
