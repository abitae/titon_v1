<div class="space-y-4">
    @if ($alerts !== [])
        <div class="space-y-1.5">
            @foreach ($alerts as $alert)
                <div
                    @class([
                        'rounded-lg border px-2.5 py-1.5 text-xs',
                        'border-rose-300 bg-rose-50 text-rose-900 dark:border-rose-800 dark:bg-rose-950/60 dark:text-rose-50' => ($alert['type'] ?? '') === 'danger',
                        'border-amber-300 bg-amber-50 text-amber-950 dark:border-amber-800 dark:bg-amber-950/50 dark:text-amber-50' => ($alert['type'] ?? '') !== 'danger',
                    ])
                >
                    {{ $alert['message'] }}
                </div>
            @endforeach
        </div>
    @endif

    <x-mechanics.page-header title="Panel de mecánica" description="Indicadores gráficos de flota, mantenimiento y órdenes de trabajo.">
        <flux:button variant="outline" size="sm" href="{{ route('mechanics.work-orders') }}" wire:navigate icon="chart-bar">OT gráficas</flux:button>
    </x-mechanics.page-header>

    @php
        $equipmentTotal = max(1, (int) $kpis['total_equipment']);
        $equipmentPercents = [
            'operational' => round(((int) $kpis['operational'] / $equipmentTotal) * 100, 1),
            'in_maintenance' => round(((int) $kpis['in_maintenance'] / $equipmentTotal) * 100, 1),
            'broken' => round(((int) $kpis['broken'] / $equipmentTotal) * 100, 1),
        ];
    @endphp

    <section class="grid grid-cols-2 gap-2 lg:grid-cols-4 xl:grid-cols-5">
        <x-mechanics.kpi-stat label="Equipos" :value="number_format($kpis['total_equipment'])" />
        <x-mechanics.kpi-stat label="Operativos" :value="number_format($kpis['operational'])" :percent="$equipmentPercents['operational']" tone="emerald" />
        <x-mechanics.kpi-stat label="En mantenimiento" :value="number_format($kpis['in_maintenance'])" :percent="$equipmentPercents['in_maintenance']" tone="amber" />
        <x-mechanics.kpi-stat label="Averiados" :value="number_format($kpis['broken'])" :percent="$equipmentPercents['broken']" tone="rose" />
        <x-mechanics.kpi-stat label="OT abiertas" :value="number_format($kpis['work_orders_open'])" tone="cyan" class="col-span-2 lg:col-span-1" />
    </section>

    <section class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        <x-mechanics.kpi-stat label="Rev. vencidas" :value="number_format($kpis['technical_expired'])" tone="rose" />
        <x-mechanics.kpi-stat label="Rev. por vencer" :value="number_format($kpis['technical_due_soon'])" tone="amber" />
        <x-mechanics.kpi-stat label="Prev. próx. 30d" :value="number_format($kpis['preventive_upcoming'])" tone="cyan" />
        <x-mechanics.kpi-stat label="Costo mant." :value="'S/ '.number_format((float) $kpis['maintenance_cost_total'], 0)" tone="amber" />
    </section>

    <div class="grid gap-3 xl:grid-cols-2">
        <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <x-charts.chart id="mech-equipment-status" title="Equipos por estado" :config="$charts['equipment_by_status']" height="280" />
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <x-charts.chart id="mech-work-orders-status" title="Órdenes de trabajo por estado" :config="$charts['work_orders_by_status']" height="280" />
        </article>
    </div>

    <div class="grid gap-3 xl:grid-cols-2">
        <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <x-charts.chart id="mech-tech" title="Revisiones vencidas vs vigentes" :config="$charts['technical_expired_vs_valid']" height="280" />
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <x-charts.chart id="mech-prev-corr" title="Preventivo vs correctivo" subtitle="Costos mensuales" :config="$charts['maintenance_preventivo_vs_correctivo']" height="280" />
        </article>
    </div>

    <div class="grid gap-3 xl:grid-cols-[1.4fr_1fr]">
        <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <x-charts.chart id="mech-cost-month" title="Costos por mes" subtitle="OT cerradas y mantenimientos sin OT" :config="$charts['cost_by_month']" height="300" />
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <x-charts.chart id="mech-cost-eq" title="Top equipos por costo" subtitle="OT cerradas" :config="$charts['cost_by_equipment']" height="300" />
        </article>
    </div>
</div>
