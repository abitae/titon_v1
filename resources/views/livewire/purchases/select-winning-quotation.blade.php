<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Seleccion de ganador</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $purchaseRequest->code }} · {{ $purchaseRequest->project?->name ?? 'Sin obra' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('purchases.comparison', $purchaseRequest) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Volver a comparativa</a>
            <a href="{{ route('purchases.orders') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Ordenes</a>
            @if ($comparison?->purchase_order_code)
                <a href="{{ route('purchases.order.pdf', $purchaseRequest) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">PDF orden</a>
            @endif
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-[1.1fr,0.9fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Elegir cotizacion ganadora</h2>
            <div class="mt-4 space-y-4">
                @forelse ($summary['quotations'] as $quotation)
                    <label class="flex cursor-pointer items-start gap-4 rounded-2xl border border-slate-200 p-4 transition hover:border-cyan-200 hover:bg-cyan-50/60 dark:border-slate-800 dark:hover:border-cyan-900/40 dark:hover:bg-cyan-950/20">
                        <input type="radio" wire:model="selected_supplier_quotation_id" value="{{ $quotation->id }}" class="mt-1">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-3">
                                <p class="font-medium text-slate-950 dark:text-white">{{ $quotation->supplier?->business_name ?? 'Sin proveedor' }}</p>
                                @if ((float) $quotation->total === (float) ($summary['min_total'] ?? 0))
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">Mejor precio</span>
                                @endif
                                @if ((int) $quotation->delivery_time === (int) ($summary['min_delivery_time'] ?? 0))
                                    <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-medium text-sky-700 dark:bg-sky-950/40 dark:text-sky-300">Mejor tiempo</span>
                                @endif
                            </div>
                            <div class="mt-3 grid gap-3 sm:grid-cols-3 text-sm text-slate-600 dark:text-slate-300">
                                <p><span class="font-medium">Codigo:</span> {{ $quotation->code }}</p>
                                <p><span class="font-medium">Total:</span> {{ $quotation->currency }} {{ number_format((float) $quotation->total, 2) }}</p>
                                <p><span class="font-medium">Entrega:</span> {{ $quotation->delivery_time }} dias</p>
                            </div>
                        </div>
                    </label>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">Todavia no existen cotizaciones para adjudicar.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Motivo de seleccion</h2>
                <textarea wire:model="selection_reason" rows="6" class="mt-4 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Explica por que se selecciona este proveedor"></textarea>
                @error('selected_supplier_quotation_id') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                @error('selection_reason') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                <button type="button" wire:click="saveSelection" class="mt-4 w-full rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Guardar ganador</button>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Orden de compra</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Genera la orden de compra directamente desde la cotizacion ganadora seleccionada.</p>
                @if ($comparison?->purchase_order_code)
                    <p class="mt-4 rounded-2xl bg-slate-100 px-4 py-3 text-sm font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $comparison->purchase_order_code }}</p>
                @endif
                <button type="button" wire:click="generateOrder" class="mt-4 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Generar orden de compra</button>
                @if ($generated_purchase_order_id)
                    <a href="{{ route('purchases.orders') }}" class="mt-3 inline-flex w-full justify-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Abrir orden generada</a>
                @endif
            </div>

            @if ($comparison?->selectedQuotation)
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
                    <h2 class="text-lg font-semibold text-emerald-800 dark:text-emerald-200">Seleccion actual</h2>
                    <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-300">{{ $comparison->selectedQuotation->supplier?->business_name }} · {{ $comparison->selectedQuotation->currency }} {{ number_format((float) $comparison->selectedQuotation->total, 2) }}</p>
                    <p class="mt-3 text-sm text-emerald-700 dark:text-emerald-300">{{ $comparison->selection_reason ?: 'Sin motivo registrado.' }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
