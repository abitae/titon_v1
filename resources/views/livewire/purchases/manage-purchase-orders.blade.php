<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Ordenes de compra</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Administra la aprobacion, observacion y anulacion de las OC generadas desde cotizaciones ganadoras.</p>
        </div>
        <flux:button variant="outline" href="{{ route('modules.purchases') }}" wire:navigate>
            Volver a requerimientos
        </flux:button>
    </div>

    <x-platform.filter-bar>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Codigo o condiciones" />
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Estado</label>
            <select wire:model.live="statusFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($statusOptions as $statusOption)
                    <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </div>
    </x-platform.filter-bar>

    <x-platform.compact-table :headers="['Codigo', 'Obra', 'Proveedor', 'Total', 'Estado', 'Acciones']">
        @forelse ($orders as $order)
            <tr class="text-sm text-slate-700 dark:text-slate-200" wire:key="purchase-order-{{ $order->id }}">
                <td class="px-6 py-4 font-medium text-slate-950 dark:text-white">{{ $order->code }}</td>
                <td class="px-6 py-4">{{ $order->project?->name ?? 'Sin obra' }}</td>
                <td class="px-6 py-4">{{ $order->supplier?->business_name ?? 'Sin proveedor' }}</td>
                <td class="px-6 py-4">{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</td>
                <td class="px-6 py-4"><x-platform.status-badge :value="$order->status" /></td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-0">
                        <flux:tooltip content="Ver orden (PDF)">
                            <flux:button
                                type="button"
                                variant="ghost"
                                size="sm"
                                icon="eye"
                                wire:click="openPdfModal({{ $order->id }})"
                                class="!size-7 !min-h-0 !p-0 !text-cyan-700 hover:!text-cyan-800 dark:!text-cyan-300 dark:hover:!text-cyan-200"
                                aria-label="Ver orden en PDF"
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
                                aria-label="Gestionar orden"
                            />
                        </flux:tooltip>
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
                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No hay ordenes de compra generadas.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-3xl border border-slate-200 bg-white px-6 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $orders->links() }}
    </div>

    <x-platform.modal compact :show="$showDetailModal" max-width="max-w-4xl">
        @if ($selectedOrder)
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ $selectedOrder->code }}</h2>
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

            <div class="mt-3 flex flex-wrap gap-1 rounded-lg border border-slate-200 bg-slate-50 p-1 dark:border-slate-800 dark:bg-slate-950/40">
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
                            'rounded-md' => true,
                            '!bg-white !text-cyan-800 shadow-sm ring-1 ring-slate-200/90 dark:!bg-slate-800 dark:!text-cyan-300 dark:ring-slate-700' => $detailModalTab === $tabKey,
                            '!text-slate-600 hover:!bg-white/80 dark:!text-slate-400 dark:hover:!bg-slate-800/60' => $detailModalTab !== $tabKey,
                        ])
                    >
                        {{ $tabLabel }}
                        @if ($tabKey === 'items')
                            <span class="ms-1 tabular-nums">({{ $selectedOrder->items->count() }})</span>
                        @endif
                    </flux:button>
                @endforeach
            </div>

            @if ($detailModalTab === 'datos')
                <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
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
                        <textarea wire:model="selectedOrder.conditions" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Observación de la orden</label>
                        <textarea wire:model="selectedOrder.observation" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-end gap-2">
                    <flux:button type="button" variant="outline" wire:click="closeModal" size="sm">Cancelar</flux:button>
                    <flux:button type="button" variant="primary" wire:click="updateOrder" size="sm">Guardar datos</flux:button>
                </div>
            @endif

            @if ($detailModalTab === 'items')
                <x-platform.compact-table
                    dense
                    :headers="['Producto', 'Und.', 'Cant.', 'Total']"
                    class="mt-3"
                >
                    @forelse ($selectedOrder->items as $item)
                        <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="purchase-order-item-{{ $item->id }}">
                            <td class="max-w-[14rem] px-2.5 py-1.5">
                                <p class="truncate font-medium text-slate-950 dark:text-white">{{ $item->product_or_service }}</p>
                            </td>
                            <td class="whitespace-nowrap px-2.5 py-1.5">{{ $item->unit }}</td>
                            <td class="whitespace-nowrap px-2.5 py-1.5 text-end tabular-nums">{{ number_format((float) $item->quantity, 2) }}</td>
                            <td class="whitespace-nowrap px-2.5 py-1.5 text-end tabular-nums font-medium text-slate-950 dark:text-white">
                                {{ $selectedOrder->currency }} {{ number_format((float) $item->total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-2.5 py-4 text-center text-[11px] text-slate-500 dark:text-slate-400">Esta orden no tiene ítems registrados.</td>
                        </tr>
                    @endforelse
                </x-platform.compact-table>
            @endif

            @if ($detailModalTab === 'acciones')
                <div class="mt-3 grid gap-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-slate-800 dark:bg-slate-950/40">
                        <h3 class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aprobación</h3>
                        <textarea wire:model="approval_notes" rows="2" class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Notas de aprobación"></textarea>
                        <flux:button type="button" variant="primary" wire:click="approveOrder" size="sm" class="mt-2 w-full">Aprobar</flux:button>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-slate-800 dark:bg-slate-950/40">
                        <h3 class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Observación</h3>
                        <textarea wire:model="observation" rows="2" class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Motivo de observación"></textarea>
                        @error('observation') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                        <flux:button type="button" variant="outline" wire:click="observeOrder" size="sm" class="mt-2 w-full">Observar</flux:button>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-slate-800 dark:bg-slate-950/40">
                        <h3 class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Anulación</h3>
                        <textarea wire:model="cancellation_reason" rows="2" class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Motivo de anulación"></textarea>
                        @error('cancellation_reason') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                        <flux:button type="button" variant="danger" wire:click="cancelOrder" size="sm" class="mt-2 w-full">Anular</flux:button>
                    </div>
                </div>

                <div class="mt-2 rounded-lg border border-slate-200 bg-white p-2.5 dark:border-slate-800 dark:bg-slate-900">
                    @if ($selectedOrder->contract)
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h3 class="text-[10px] font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Contrato generado</h3>
                                <p class="mt-1 text-xs font-medium text-slate-950 dark:text-white">{{ $selectedOrder->contract->contract_number }}</p>
                            </div>
                            <flux:button variant="outline" href="{{ route('modules.contracts') }}" wire:navigate size="sm">Ver contratos</flux:button>
                        </div>
                    @else
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-[11px] text-slate-600 dark:text-slate-300">Convierta la orden aprobada en contrato con proveedor.</p>
                            <flux:button type="button" variant="outline" wire:click="createContract" size="sm">Convertir a contrato</flux:button>
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
