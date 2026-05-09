<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Ordenes de compra</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Administra la aprobacion, observacion y anulacion de las OC generadas desde cotizaciones ganadoras.</p>
        </div>
        <a href="{{ route('modules.purchases') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Volver a compras</a>
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
                    <div class="flex flex-wrap gap-2 justify-end">
                        <button type="button" wire:click="openDetailModal({{ $order->id }})" class="rounded-lg px-2 py-1 text-sm font-medium text-cyan-700 hover:bg-cyan-50 dark:text-cyan-300">Ver</button>
                        <a href="{{ route('purchases.orders.pdf', $order) }}" class="rounded-lg px-2 py-1 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200">PDF</a>
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

    <x-platform.modal :show="$showDetailModal" max-width="max-w-6xl">
        @if ($selectedOrder)
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $selectedOrder->code }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $selectedOrder->supplier?->business_name ?? 'Sin proveedor' }} · {{ $selectedOrder->project?->name ?? 'Sin obra' }}</p>
                </div>
                <button type="button" wire:click="closeModal" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-[1.1fr,0.9fr]">
                <div class="space-y-4">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/30">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Fecha</label>
                                <input wire:model="selectedOrder.issue_date" type="date" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Moneda</label>
                                <input wire:model="selectedOrder.currency" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Estado</label>
                                <select wire:model="selectedOrder.status" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                    @foreach ($statusOptions as $statusOption)
                                        <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Total</label>
                                <input value="{{ $selectedOrder->currency }} {{ number_format((float) $selectedOrder->total, 2) }}" disabled class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Condiciones</label>
                                <textarea wire:model="selectedOrder.conditions" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Observacion</label>
                                <textarea wire:model="selectedOrder.observation" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                            </div>
                        </div>
                        <button type="button" wire:click="updateOrder" class="mt-4 rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Guardar cambios</button>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Items</h3>
                        <div class="mt-4 space-y-3">
                            @foreach ($selectedOrder->items as $item)
                                <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                                    <p class="font-medium text-slate-950 dark:text-white">{{ $item->product_or_service }}</p>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $item->quantity }} {{ $item->unit }} · {{ $selectedOrder->currency }} {{ number_format((float) $item->total, 2) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Aprobacion</h3>
                        <textarea wire:model="approval_notes" rows="3" class="mt-4 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Notas de aprobacion"></textarea>
                        <button type="button" wire:click="approveOrder" class="mt-4 w-full rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Aprobar orden</button>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Observacion</h3>
                        <textarea wire:model="observation" rows="3" class="mt-4 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Motivo de observacion"></textarea>
                        @error('observation') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        <button type="button" wire:click="observeOrder" class="mt-4 w-full rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-700 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-300">Observar orden</button>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Anulacion</h3>
                        <textarea wire:model="cancellation_reason" rows="3" class="mt-4 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Motivo de anulacion"></textarea>
                        @error('cancellation_reason') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        <button type="button" wire:click="cancelOrder" class="mt-4 w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300">Anular orden</button>
                    </div>

                    @if ($selectedOrder->contract)
                        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
                            <h3 class="text-lg font-semibold text-emerald-800 dark:text-emerald-200">Contrato generado</h3>
                            <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-300">{{ $selectedOrder->contract->contract_number }}</p>
                            <a href="{{ route('modules.contracts') }}" class="mt-3 inline-flex rounded-xl border border-emerald-300 px-4 py-2 text-sm font-medium text-emerald-700 dark:border-emerald-800 dark:text-emerald-300">Ver contratos</a>
                        </div>
                    @else
                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Contrato</h3>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Convierte esta orden aprobada en contrato con proveedor para continuar el seguimiento.</p>
                            <button type="button" wire:click="createContract" class="mt-4 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Convertir a contrato</button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </x-platform.modal>
</div>
