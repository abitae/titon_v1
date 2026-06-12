<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Comparativa visual</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $purchaseRequest->code }} · {{ $purchaseRequest->project?->name ?? 'Sin obra' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('purchases.quotations', $purchaseRequest) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Cotizaciones</a>
            @can('purchases.aprobar')
                <flux:button type="button" variant="outline" wire:click="openWinnerModal">Seleccionar ganador</flux:button>
            @endcan
            <a href="{{ route('purchases.comparison.pdf', $purchaseRequest) }}" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">PDF comparativa</a>
        </div>
    </div>

    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Cotizaciones comparadas</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ $summary['quotations']->count() }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Mejor precio</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format((float) ($summary['min_total'] ?? 0), 2) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Entrega mas rapida</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ $summary['min_delivery_time'] ?? 0 }} dias</p>
        </div>
    </section>

    <x-platform.compact-table :headers="['Proveedor', 'Código', 'Total', 'Entrega', 'Pago', 'Garantía']">
        @forelse ($summary['quotations'] as $quotation)
            <tr wire:key="comparison-quotation-{{ $quotation->id }}">
                <td class="font-medium text-slate-950 dark:text-white">{{ $quotation->supplier?->business_name ?? 'Sin proveedor' }}</td>
                <td>{{ $quotation->code }}</td>
                <td>
                    <span @class([
                        'rounded-full px-2 py-0.5 text-[10px] font-medium',
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' => (float) $quotation->total === (float) ($summary['min_total'] ?? 0),
                    ])>
                        {{ $quotation->currency }} {{ number_format((float) $quotation->total, 2) }}
                    </span>
                </td>
                <td>
                    <span @class([
                        'rounded-full px-2 py-0.5 text-[10px] font-medium',
                        'bg-sky-100 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300' => (int) $quotation->delivery_time_days === (int) ($summary['min_delivery_time'] ?? 0),
                    ])>
                        {{ $quotation->delivery_time_days }} días
                    </span>
                </td>
                <td>{{ $quotation->payment_conditions ?: 'Sin condición' }}</td>
                <td>{{ $quotation->warranty ?: 'Sin garantía' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="!py-5 text-center text-[11px] text-slate-500 dark:text-slate-400">Todavía no hay cotizaciones para comparar.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    @if ($comparison?->selectedQuotation)
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
            <h2 class="text-lg font-semibold text-emerald-800 dark:text-emerald-200">Proveedor ganador actual</h2>
            <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-300">{{ $comparison->selectedQuotation->supplier?->business_name }} · {{ $comparison->selectedQuotation->currency }} {{ number_format((float) $comparison->selectedQuotation->total, 2) }}</p>
            <p class="mt-3 text-sm text-emerald-700 dark:text-emerald-300">{{ $comparison->selection_reason ?: 'Sin motivo registrado.' }}</p>
        </div>
    @endif

    @include('livewire.purchases.partials.select-winner-modal', [
        'summary' => $summary,
        'comparison' => $comparison,
    ])
</div>
