@php
    /** @var \App\Models\PurchaseRequest $purchaseRequest */
    /** @var array<string, mixed> $summary */
    $comparisonQuotations = $summary['quotations'];
@endphp

@if ($showComparisonModal)
    <div class="fixed inset-0 z-[110] flex flex-col bg-slate-50 dark:bg-slate-950">
        <div class="flex shrink-0 items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-950/95">
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-slate-950 dark:text-white">Comparativa de cotizaciones</h2>
                <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                    {{ $purchaseRequest->code }} · {{ $purchaseRequest->project?->name ?? 'Sin obra' }}
                    · {{ $comparisonQuotations->count() }} cotización(es) seleccionada(s)
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <div class="hidden items-center gap-3 text-[11px] text-slate-600 sm:flex dark:text-slate-300">
                    <span>Mejor precio: <strong class="text-emerald-700 dark:text-emerald-300">{{ number_format((float) ($summary['min_total'] ?? 0), 2) }}</strong></span>
                    <span>Mejor entrega: <strong class="text-sky-700 dark:text-sky-300">{{ $summary['min_delivery_time'] ?? 0 }} d</strong></span>
                </div>
                @can('purchases.aprobar')
                    <flux:button type="button" variant="outline" size="sm" wire:click="openWinnerModal">Seleccionar ganador</flux:button>
                @endcan
                <flux:tooltip content="Cerrar">
                    <flux:button type="button" variant="ghost" size="sm" icon="x-mark" wire:click="closeComparisonModal" aria-label="Cerrar comparativa" />
                </flux:tooltip>
            </div>
        </div>

        @if ($comparisonQuotations->isEmpty())
            <div class="flex flex-1 items-center justify-center p-6">
                <p class="text-sm text-slate-500 dark:text-slate-400">Seleccione al menos 2 cotizaciones para comparar.</p>
            </div>
        @else
            <div class="grid min-h-0 flex-1 grid-cols-1 gap-2 overflow-y-auto bg-slate-100/80 p-2 dark:bg-slate-950 lg:grid-cols-2">
                @foreach ($comparisonQuotations as $quotation)
                    @php
                        $pdfUrl = $quotation->quotationPdfPreviewUrl();
                        $isBestPrice = (float) $quotation->total === (float) ($summary['min_total'] ?? 0);
                        $isBestDelivery = (int) $quotation->delivery_time_days === (int) ($summary['min_delivery_time'] ?? 0);
                    @endphp
                    <div
                        wire:key="comparison-panel-{{ $quotation->id }}"
                        class="flex min-h-[40vh] flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-none lg:min-h-0"
                    >
                        <div class="flex shrink-0 items-start justify-between gap-2 border-b border-slate-200 px-3 py-2 dark:border-slate-800">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-950 dark:text-white">{{ $quotation->supplier?->business_name ?? 'Sin proveedor' }}</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $quotation->code }} · {{ $quotation->currency }} {{ number_format((float) $quotation->total, 2) }}</p>
                            </div>
                            <div class="flex shrink-0 flex-wrap justify-end gap-1">
                                @if ($isBestPrice)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300">Mejor precio</span>
                                @endif
                                @if ($isBestDelivery)
                                    <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-medium text-sky-700 dark:bg-sky-950/80 dark:text-sky-300">Mejor entrega</span>
                                @endif
                                @if ($pdfUrl)
                                    <flux:tooltip content="Abrir PDF en pestaña nueva">
                                        <flux:button variant="ghost" size="sm" icon="arrow-top-right-on-square" href="{{ $pdfUrl }}" target="_blank" class="!size-7 !min-h-0 !p-0" aria-label="Abrir PDF" />
                                    </flux:tooltip>
                                @endif
                            </div>
                        </div>

                        <div wire:ignore.self wire:key="comparison-pdf-{{ $quotation->id }}-{{ md5($pdfUrl) }}" class="min-h-0 flex-1 bg-slate-100 dark:bg-slate-950">
                            <iframe
                                src="{{ $pdfUrl }}"
                                title="Cotización {{ $quotation->code }}"
                                class="h-full w-full"
                                loading="lazy"
                            ></iframe>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif
