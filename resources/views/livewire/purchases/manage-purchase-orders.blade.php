<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold text-slate-950 dark:text-white">Órdenes de compra</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Aprobación, observación y anulación de OC generadas desde cotizaciones ganadoras.</p>
        </div>
        <flux:button variant="outline" href="{{ route('modules.purchases') }}" wire:navigate size="sm">
            Requerimientos
        </flux:button>
    </div>

    <div class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="min-w-[10rem] flex-1">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Código o condiciones" />
        </div>
        <div class="w-36">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</label>
            <select wire:model.live="statusFilter" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($statusOptions as $statusOption)
                    <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <x-platform.compact-table dense :headers="['Orden', 'Obra / Proveedor', 'Total', 'Estado', '']">
        @forelse ($orders as $order)
            <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="purchase-order-{{ $order->id }}">
                <td class="whitespace-nowrap px-2.5 py-1.5">
                    <p class="font-semibold leading-tight text-slate-950 dark:text-white">{{ $order->code }}</p>
                    <p class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400">{{ $order->issue_date?->format('d/m/y') ?? '—' }}</p>
                </td>
                <td class="max-w-[14rem] px-2.5 py-1.5">
                    <p class="truncate font-medium leading-tight text-slate-950 dark:text-white">{{ $order->project?->name ?? 'Sin obra' }}</p>
                    <p class="mt-0.5 truncate text-[10px] text-slate-500 dark:text-slate-400">{{ $order->supplier?->business_name ?? 'Sin proveedor' }}</p>
                </td>
                <td class="whitespace-nowrap px-2.5 py-1.5 tabular-nums">
                    <span class="font-medium text-slate-950 dark:text-white">{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</span>
                </td>
                <td class="whitespace-nowrap px-2.5 py-1.5">
                    <x-platform.status-badge :value="$order->status" size="xs" />
                </td>
                <td class="whitespace-nowrap px-1.5 py-1">
                    <div class="flex items-center justify-end gap-0">
                        <flux:tooltip content="{{ $order->hasAttachedQuotationPdf() ? 'Ver PDF con cotización adjunta' : 'Ver PDF' }}">
                            <flux:button
                                type="button"
                                variant="ghost"
                                size="sm"
                                icon="eye"
                                wire:click="openPdfModal({{ $order->id }})"
                                class="!size-7 !min-h-0 !p-0 !text-cyan-700 hover:!text-cyan-800 dark:!text-cyan-300 dark:hover:!text-cyan-200"
                                aria-label="Ver PDF"
                            />
                        </flux:tooltip>
                        <flux:tooltip content="Gestionar">
                            <flux:button
                                type="button"
                                variant="ghost"
                                size="sm"
                                icon="pencil-square"
                                wire:click="openDetailModal({{ $order->id }})"
                                class="!size-7 !min-h-0 !p-0"
                                aria-label="Gestionar"
                            />
                        </flux:tooltip>
                        @if ($order->accountsPayable)
                            <flux:tooltip content="Cuenta por pagar">
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="banknotes"
                                    href="{{ route('accounts-payable.show', $order->accountsPayable) }}"
                                    wire:navigate
                                    class="!size-7 !min-h-0 !p-0 !text-violet-700 hover:!text-violet-800 dark:!text-violet-300 dark:hover:!text-violet-200"
                                    aria-label="Ver cuenta por pagar"
                                />
                            </flux:tooltip>
                        @else
                            <flux:tooltip content="Conformidad">
                                <flux:button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    icon="clipboard-document-check"
                                    wire:click="openConformityModal({{ $order->id }})"
                                    class="!size-7 !min-h-0 !p-0 !text-emerald-600 hover:!text-emerald-700 dark:!text-emerald-400 dark:hover:!text-emerald-300"
                                    aria-label="Conformidad"
                                />
                            </flux:tooltip>
                        @endif
                        <flux:tooltip content="Descargar PDF">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="arrow-down-tray"
                                href="{{ route('purchases.orders.pdf', $order) }}"
                                target="_blank"
                                class="!size-7 !min-h-0 !p-0"
                                aria-label="Descargar PDF"
                            />
                        </flux:tooltip>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-2.5 py-6 text-center text-[11px] text-slate-500 dark:text-slate-400">No hay órdenes de compra generadas.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $orders->links() }}
    </div>

    <x-platform.modal compact :show="$showDetailModal" max-width="max-w-3xl">
        @if ($selectedOrder)
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ $selectedOrder->code }}</h2>
                        <x-platform.status-badge :value="$selectedOrder->status" size="xs" />
                    </div>
                    <p class="mt-0.5 truncate text-[11px] text-slate-500 dark:text-slate-400">
                        {{ $selectedOrder->supplier?->business_name ?? 'Sin proveedor' }}
                        · {{ $selectedOrder->project?->name ?? 'Sin obra' }}
                        · {{ $selectedOrder->currency }} {{ number_format((float) $selectedOrder->total, 2) }}
                    </p>
                </div>
                <flux:tooltip content="Cerrar">
                    <flux:button type="button" variant="ghost" size="sm" icon="x-mark" wire:click="closeModal" class="!size-7 !min-h-0 !p-0" aria-label="Cerrar" />
                </flux:tooltip>
            </div>

            <div class="mt-2 flex flex-wrap gap-1 rounded-lg border border-slate-200 bg-slate-50 p-0.5 dark:border-slate-800 dark:bg-slate-950/40">
                @foreach ([
                    'datos' => 'Datos',
                    'items' => 'Ítems',
                    'acciones' => 'Gestión',
                ] as $tabKey => $tabLabel)
                    <flux:button
                        type="button"
                        wire:click="$set('detailModalTab', '{{ $tabKey }}')"
                        size="sm"
                        @class([
                            'rounded-md !px-2.5 !py-1 !text-[11px]' => true,
                            '!bg-white !text-cyan-800 shadow-sm ring-1 ring-slate-200/90 dark:!bg-slate-800 dark:!text-cyan-300 dark:ring-slate-700' => $detailModalTab === $tabKey,
                            '!text-slate-600 hover:!bg-white/80 dark:!text-slate-400 dark:hover:!bg-slate-800/60' => $detailModalTab !== $tabKey,
                        ])
                    >
                        {{ $tabLabel }}
                        @if ($tabKey === 'items')
                            <span class="ms-0.5 tabular-nums">({{ $selectedOrder->items->count() }})</span>
                        @endif
                    </flux:button>
                @endforeach
            </div>

            @if ($detailModalTab === 'datos')
                <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fecha</label>
                        <input wire:model="selectedOrder.issue_date" type="date" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Moneda</label>
                        <input wire:model="selectedOrder.currency" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</label>
                        <select wire:model="selectedOrder.status" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            @foreach ($statusOptions as $statusOption)
                                <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total</label>
                        <input value="{{ $selectedOrder->currency }} {{ number_format((float) $selectedOrder->total, 2) }}" disabled class="mt-1 block h-8 w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 px-2 text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Condiciones</label>
                        <textarea wire:model="selectedOrder.conditions" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Observación</label>
                        <textarea wire:model="selectedOrder.observation" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                    </div>
                </div>

                <div class="mt-2 flex items-center justify-end gap-2">
                    <flux:button type="button" variant="outline" wire:click="closeModal" size="sm">Cancelar</flux:button>
                    <flux:button type="button" variant="primary" wire:click="updateOrder" size="sm">Guardar</flux:button>
                </div>
            @endif

            @if ($detailModalTab === 'items')
                <x-platform.compact-table
                    dense
                    :headers="['Producto', 'Und.', 'Cant.', 'Total']"
                    class="mt-2"
                >
                    @forelse ($selectedOrder->items as $item)
                        <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="purchase-order-item-{{ $item->id }}">
                            <td class="max-w-[12rem] px-2.5 py-1.5">
                                <p class="truncate font-medium text-slate-950 dark:text-white">{{ $item->description ?: $item->product_or_service }}</p>
                            </td>
                            <td class="whitespace-nowrap px-2.5 py-1.5">{{ $item->unit }}</td>
                            <td class="whitespace-nowrap px-2.5 py-1.5 text-end tabular-nums">{{ number_format((float) $item->quantity, 2) }}</td>
                            <td class="whitespace-nowrap px-2.5 py-1.5 text-end tabular-nums font-medium text-slate-950 dark:text-white">
                                {{ $selectedOrder->currency }} {{ number_format((float) $item->total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-2.5 py-3 text-center text-[11px] text-slate-500 dark:text-slate-400">Sin ítems.</td>
                        </tr>
                    @endforelse
                </x-platform.compact-table>
            @endif

            @if ($detailModalTab === 'acciones')
                <div class="mt-2 grid gap-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2 dark:border-slate-800 dark:bg-slate-950/40">
                        <h3 class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aprobación</h3>
                        <textarea wire:model="approval_notes" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Notas"></textarea>
                        <flux:button type="button" variant="primary" wire:click="approveOrder" size="sm" class="mt-1.5 w-full">Aprobar</flux:button>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2 dark:border-slate-800 dark:bg-slate-950/40">
                        <h3 class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Observación</h3>
                        <textarea wire:model="observation" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Motivo"></textarea>
                        @error('observation') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                        <flux:button type="button" variant="outline" wire:click="observeOrder" size="sm" class="mt-1.5 w-full">Observar</flux:button>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2 dark:border-slate-800 dark:bg-slate-950/40">
                        <h3 class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Anulación</h3>
                        <textarea wire:model="cancellation_reason" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Motivo"></textarea>
                        @error('cancellation_reason') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                        <flux:button type="button" variant="danger" wire:click="cancelOrder" size="sm" class="mt-1.5 w-full">Anular</flux:button>
                    </div>
                </div>

                <div class="mt-2 rounded-lg border border-slate-200 p-2 dark:border-slate-800 dark:bg-slate-900">
                    @if ($selectedOrder->contract)
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Contrato</p>
                                <p class="text-xs font-medium text-slate-950 dark:text-white">{{ $selectedOrder->contract->contract_number }}</p>
                            </div>
                            <flux:button variant="outline" href="{{ route('modules.contracts') }}" wire:navigate size="sm">Ver</flux:button>
                        </div>
                    @else
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-[11px] text-slate-600 dark:text-slate-300">Convertir a contrato con proveedor.</p>
                            <flux:button type="button" variant="outline" wire:click="createContract" size="sm">Crear contrato</flux:button>
                        </div>
                    @endif
                </div>
            @endif
        @endif
    </x-platform.modal>

    <x-platform.pdf-viewer-modal
        :show="$showPdfModal"
        :url="$pdfViewerUrl"
        :title="$pdfViewerTitle"
        subtitle="Vista previa de la orden de compra"
    />

    @include('livewire.purchases.partials.conformity-modal')
</div>
