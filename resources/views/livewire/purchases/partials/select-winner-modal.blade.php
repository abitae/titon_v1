@php
    /** @var \App\Models\PurchaseRequest $purchaseRequest */
    /** @var array<string, mixed> $summary */
    /** @var \App\Models\QuotationComparison|null $comparison */
@endphp

<x-platform.modal compact layer="top" :show="$showWinnerModal" max-width="max-w-3xl">
    <div class="flex items-center justify-between gap-2">
        <div>
            <h2 class="text-base font-semibold text-slate-950 dark:text-white">Seleccionar ganador</h2>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $purchaseRequest->code }} · Elija la cotización ganadora</p>
        </div>
        <flux:tooltip content="Cerrar">
            <flux:button type="button" variant="ghost" size="sm" icon="x-mark" wire:click="closeWinnerModal" class="!size-7 !min-h-0 !p-0" aria-label="Cerrar" />
        </flux:tooltip>
    </div>

    <div class="mt-3 max-h-[40vh] space-y-2 overflow-y-auto pr-1">
        @forelse ($summary['quotations'] as $quotation)
            <label
                wire:key="winner-option-{{ $quotation->id }}"
                class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-3 transition hover:border-cyan-200 hover:bg-cyan-50/60 dark:border-slate-800 dark:hover:border-cyan-900/40 dark:hover:bg-cyan-950/20"
            >
                <input type="radio" wire:model="selected_supplier_quotation_id" value="{{ $quotation->id }}" class="mt-0.5">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-medium text-slate-950 dark:text-white">{{ $quotation->supplier?->business_name ?? 'Sin proveedor' }}</p>
                        @if ((float) $quotation->total === (float) ($summary['min_total'] ?? 0))
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">Mejor precio</span>
                        @endif
                        @if ((int) $quotation->delivery_time_days === (int) ($summary['min_delivery_time'] ?? 0))
                            <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-medium text-sky-700 dark:bg-sky-950/40 dark:text-sky-300">Mejor tiempo</span>
                        @endif
                    </div>
                    <div class="mt-1.5 grid gap-1 text-[11px] text-slate-600 sm:grid-cols-3 dark:text-slate-300">
                        <p><span class="font-medium text-slate-500 dark:text-slate-400">Código:</span> {{ $quotation->code }}</p>
                        <p><span class="font-medium text-slate-500 dark:text-slate-400">Total:</span> {{ $quotation->currency }} {{ number_format((float) $quotation->total, 2) }}</p>
                        <p><span class="font-medium text-slate-500 dark:text-slate-400">Entrega:</span> {{ $quotation->delivery_time_days }} días</p>
                    </div>
                </div>
            </label>
        @empty
            <p class="rounded-lg border border-dashed border-slate-200 px-3 py-6 text-center text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
                Todavía no hay cotizaciones para adjudicar.
            </p>
        @endforelse
    </div>

    <div class="mt-3">
        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Motivo de selección</label>
        <textarea
            wire:model="selection_reason"
            rows="3"
            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('selection_reason') border-rose-500 @enderror"
            placeholder="Explique por qué se selecciona este proveedor"
        ></textarea>
        @error('selected_supplier_quotation_id') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
        @error('selection_reason') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
    </div>

    @if ($comparison?->selectedQuotation)
        <div class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 dark:border-emerald-900/40 dark:bg-emerald-950/20">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Ganador actual</p>
            <p class="mt-1 text-xs text-emerald-800 dark:text-emerald-200">
                {{ $comparison->selectedQuotation->supplier?->business_name }}
                · {{ $comparison->selectedQuotation->currency }} {{ number_format((float) $comparison->selectedQuotation->total, 2) }}
            </p>
        </div>
    @endif

    <div class="mt-3 flex flex-wrap items-center justify-end gap-2">
        @if ($comparison?->order_code)
            <span class="me-auto rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $comparison->order_code }}</span>
        @endif
        <flux:button type="button" variant="outline" wire:click="closeWinnerModal" size="sm">Cancelar</flux:button>
        <flux:button type="button" variant="outline" wire:click="generateOrder" size="sm">Generar OC</flux:button>
        <flux:button type="button" variant="primary" wire:click="saveSelection" size="sm">Guardar ganador</flux:button>
    </div>
</x-platform.modal>
