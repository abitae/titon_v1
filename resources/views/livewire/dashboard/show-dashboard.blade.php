@php
    $kpis = $analytics['kpis'];
    $scopeLabel = $analytics['scope_label'];
    $currency = fn (float $amount): string => 'S/ '.number_format($amount, 2);
@endphp

<div class="flex flex-1 flex-col gap-6">
    <section class="overflow-hidden rounded-[2rem] border border-white/40 bg-[radial-gradient(circle_at_top_left,_rgba(45,212,191,0.3),_transparent_28%),linear-gradient(135deg,_#020617_0%,_#0f172a_58%,_#164e63_100%)] px-6 py-8 text-white shadow-2xl shadow-slate-950/15 md:px-8">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-cyan-100">
                    Panel Ejecutivo
                </div>

                <div class="space-y-3">
                    <h1 class="text-3xl font-semibold tracking-tight text-balance md:text-4xl">{{ $title }}</h1>
                    <p class="max-w-2xl text-sm leading-6 text-slate-200 md:text-base">
                        {{ $subtitle }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-sm text-cyan-50/90">
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1">{{ $scopeLabel }}</span>
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1">{{ now()->translatedFormat('d \d\e F, Y') }}</span>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                <button
                    type="button"
                    wire:click="setMode('company')"
                    @class([
                        'rounded-2xl px-4 py-2 text-sm font-medium transition',
                        'bg-white text-slate-950 shadow-sm' => $analytics['mode'] === 'company',
                        'border border-white/20 bg-white/10 text-white hover:bg-white/15' => $analytics['mode'] !== 'company',
                    ])
                >
                    Empresa activa
                </button>

                @if ($analytics['can_view_consolidated'])
                    <button
                        type="button"
                        wire:click="setMode('consolidated')"
                        @class([
                            'rounded-2xl px-4 py-2 text-sm font-medium transition',
                            'bg-white text-slate-950 shadow-sm' => $analytics['mode'] === 'consolidated',
                            'border border-white/20 bg-white/10 text-white hover:bg-white/15' => $analytics['mode'] !== 'consolidated',
                        ])
                    >
                        Consolidado gerencial
                    </button>
                @endif

                <a
                    href="{{ route('reports.dashboard.pdf', ['mode' => $analytics['mode']]) }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-cyan-300/30 bg-cyan-300/10 px-4 py-2 text-sm font-medium text-cyan-50 transition hover:bg-cyan-300/20"
                >
                    Exportar resumen PDF
                </a>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Obras activas</p>
            <p class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($kpis['active_projects']) }}</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Operaci&oacute;n en curso dentro del alcance seleccionado.</p>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total contratado</p>
            <p class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ $currency($kpis['contracted_total']) }}</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Monto acumulado de contratos con proveedores.</p>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total pagado</p>
            <p class="mt-3 text-3xl font-semibold text-emerald-700 dark:text-emerald-300">{{ $currency($kpis['paid_total']) }}</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Desembolso registrado en pagos asociados a contratos.</p>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Saldo pendiente</p>
            <p class="mt-3 text-3xl font-semibold text-amber-600 dark:text-amber-300">{{ $currency($kpis['pending_balance']) }}</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Brecha entre lo contratado y lo efectivamente pagado.</p>
        </article>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Pagos vencidos</p>
                <x-platform.status-badge value="vencido" />
            </div>
            <p class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($kpis['overdue_payments']) }}</p>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Contratos activos</p>
                <x-platform.status-badge value="en_ejecucion" />
            </div>
            <p class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($kpis['active_contracts']) }}</p>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Solicitudes pendientes</p>
                <x-platform.status-badge value="borrador" />
            </div>
            <p class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($kpis['pending_requests']) }}</p>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Documentos vencidos</p>
                <x-platform.status-badge value="vencido" />
            </div>
            <p class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($kpis['expired_documents']) }}</p>
        </article>
    </section>

    <section class="grid gap-4 xl:grid-cols-[1.4fr_1fr]">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <x-charts.chart
                id="payments-by-month"
                title="Pagos por mes"
                subtitle="Tendencia de desembolsos del periodo reciente."
                :config="$analytics['charts']['payments_by_month']"
                height="320"
            />
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <x-charts.chart
                id="contracts-by-status"
                title="Contratos por estado"
                subtitle="Distribuci&oacute;n operativa del pipeline contractual."
                :config="$analytics['charts']['contracts_by_status']"
                height="320"
            />
        </article>
    </section>

    <section class="grid gap-4 xl:grid-cols-3">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 xl:col-span-2">
            <x-charts.chart
                id="projects-by-city"
                title="Obras por ciudad"
                subtitle="Carga operativa distribuida geogr&aacute;ficamente."
                :config="$analytics['charts']['projects_by_city']"
                height="320"
            />
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <x-charts.chart
                id="contracted-vs-paid"
                title="Contratado vs pagado"
                subtitle="Lectura financiera r&aacute;pida del alcance seleccionado."
                :config="$analytics['charts']['contracted_vs_paid']"
                height="320"
            />
        </article>
    </section>

    <section class="grid gap-4 xl:grid-cols-[1.15fr_0.85fr]">
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <x-charts.chart
                id="top-suppliers"
                title="Proveedores con mayor monto contratado"
                subtitle="Concentraci&oacute;n de contratos por proveedor."
                :config="$analytics['charts']['top_suppliers']"
                height="340"
            />
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Alertas ejecutivas</p>
                    <h2 class="mt-1 text-xl font-semibold text-slate-950 dark:text-white">Puntos que conviene mirar hoy</h2>
                </div>

                <flux:badge color="cyan" size="sm">Live</flux:badge>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($analytics['highlights'] as $highlight)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/60">
                        <p class="text-xs font-medium uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">{{ $highlight['label'] }}</p>
                        <p class="mt-2 text-base font-semibold text-slate-950 dark:text-white">{{ $highlight['value'] }}</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $highlight['meta'] }}</p>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-400">
                        Aun no hay suficiente informaci&oacute;n para construir alertas ejecutivas.
                    </div>
                @endforelse
            </div>
        </article>
    </section>
</div>
