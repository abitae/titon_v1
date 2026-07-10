<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase text-cyan-700 dark:text-cyan-400">Operacion</p>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ $title }}</h1>
            <p class="text-sm text-slate-600 dark:text-slate-300">Inventario por obra con ingreso automatico al registrar conformidad de OC/OS.</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="button" wire:click="setTab('stock')" @class([
            'rounded-xl px-4 py-2 text-sm font-medium',
            'bg-slate-950 text-white dark:bg-cyan-500 dark:text-slate-950' => $activeTab === 'stock',
            'border dark:border-slate-600' => $activeTab !== 'stock',
        ])>Stock por obra</button>
        <button type="button" wire:click="setTab('kardex')" @class([
            'rounded-xl px-4 py-2 text-sm font-medium',
            'bg-slate-950 text-white dark:bg-cyan-500 dark:text-slate-950' => $activeTab === 'kardex',
            'border dark:border-slate-600' => $activeTab !== 'kardex',
        ])>Kardex</button>
    </div>

    <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
            <select wire:model.live="filter_work_project_id" class="rounded-xl border px-2 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-950">
                <option value="">Todas las obras</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->code }} — {{ $project->name }}</option>
                @endforeach
            </select>

            @if ($activeTab === 'kardex')
                <select wire:model.live="filter_responsible_user_id" class="rounded-xl border px-2 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <option value="">Todos los responsables</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            @endif

            <select wire:model.live="filter_item_type" class="rounded-xl border px-2 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-950">
                <option value="">Material y servicio</option>
                @foreach ($itemTypes as $type)
                    <option value="{{ $type->value() }}">{{ $type->label() }}</option>
                @endforeach
            </select>

            <input wire:model.live.debounce.300ms="filter_description" type="search" placeholder="Buscar descripcion..." class="rounded-xl border px-2 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-950" />

            @if ($activeTab === 'kardex')
                <select wire:model.live="filter_source" class="rounded-xl border px-2 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <option value="">Todos los origenes</option>
                    @foreach ($movementSources as $source)
                        <option value="{{ $source->value() }}">{{ $source->label() }}</option>
                    @endforeach
                </select>

                <input wire:model.live.debounce.300ms="filter_order_code" type="search" placeholder="Codigo OC/OS..." class="rounded-xl border px-2 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                <input wire:model.live.debounce.300ms="filter_transfer_code" type="search" placeholder="Codigo transferencia..." class="rounded-xl border px-2 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                <input wire:model.live="filter_date_from" type="date" class="rounded-xl border px-2 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                <input wire:model.live="filter_date_to" type="date" class="rounded-xl border px-2 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
            @endif
        </div>

        <div class="mt-3">
            <button type="button" wire:click="clearFilters" class="text-xs font-medium text-cyan-700 dark:text-cyan-400">Limpiar filtros</button>
        </div>
    </div>

    @if ($activeTab === 'stock')
        <x-platform.compact-table :headers="['Obra', 'Tipo', 'Descripcion', 'Stock', 'Costo unit.', '']">
            @forelse ($stockItems as $item)
                <tr wire:key="stock-{{ $item->id }}">
                    <td>{{ $item->project?->code }}</td>
                    <td>{{ $item->item_type === 'material' ? 'Material' : 'Servicio' }}</td>
                    <td>{{ $item->description }} <span class="text-slate-500">({{ $item->unit }})</span></td>
                    <td class="tabular-nums font-medium">{{ $item->stock_quantity }} {{ $item->unit }}</td>
                    <td class="tabular-nums">S/ {{ number_format((float) $item->unit_cost, 4) }}</td>
                    <td class="!px-1.5 !py-1">
                        <x-platform.action-buttons :edit="'openKardexModal('.$item->id.')'" />
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="!py-5 text-center text-[11px] text-slate-500">Sin stock registrado.</td></tr>
            @endforelse
        </x-platform.compact-table>
        {{ $stockItems->links() }}
    @else
        <x-platform.compact-table :headers="['Fecha', 'Codigo', 'Obra', 'Descripcion', 'Origen', 'Dir.', 'Cant.', 'Responsable']">
            @forelse ($movements as $movement)
                <tr wire:key="mov-{{ $movement->id }}">
                    <td>{{ $movement->movement_date?->format('d/m/Y') }}</td>
                    <td class="font-medium">{{ $movement->movement_code }}</td>
                    <td>{{ $movement->stockItem?->project?->code }}</td>
                    <td>{{ $movement->stockItem?->description }}</td>
                    <td>
                        {{ collect($movementSources)->first(fn ($s) => $s->value() === $movement->source)?->label() ?? $movement->source }}
                        @if ($movement->order)
                            <span class="block text-[10px] text-slate-500">{{ $movement->order->code }}</span>
                        @endif
                        @if ($movement->transfer)
                            <span class="block text-[10px] text-slate-500">{{ $movement->transfer->transfer_code }}</span>
                        @endif
                    </td>
                    <td>{{ $movement->direction === 'entrada' ? 'Entrada' : 'Salida' }}</td>
                    <td class="tabular-nums">{{ $movement->quantity }}</td>
                    <td>{{ $movement->responsible?->name }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="!py-5 text-center text-[11px] text-slate-500">Sin movimientos.</td></tr>
            @endforelse
        </x-platform.compact-table>
        {{ $movements->links() }}
    @endif

    @if ($showOutboundModal && $selectedStockItem)
        <div class="fixed inset-0 z-50 flex justify-center overflow-y-auto bg-slate-950/60 px-4 py-8 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-3xl border bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex justify-between">
                    <h2 class="text-lg font-semibold dark:text-white">Salida manual</h2>
                    <button wire:click="closeOutboundModal" type="button">Cerrar</button>
                </div>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $selectedStockItem->description }} — Stock: {{ $selectedStockItem->stock_quantity }} {{ $selectedStockItem->unit }}</p>
                <div class="mt-4 grid gap-3">
                    <input wire:model="outbound_quantity" type="number" step="0.001" min="0.001" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Cantidad" />
                    <input wire:model="outbound_date" type="date" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" />
                    <textarea wire:model="outbound_reference" rows="2" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Motivo / referencia"></textarea>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="closeOutboundModal" type="button" class="rounded-xl border px-4 py-2 text-sm">Cancelar</button>
                    <button wire:click="saveOutbound" type="button" class="rounded-xl bg-slate-950 px-4 py-2 text-sm text-white dark:bg-cyan-500 dark:text-slate-950">Registrar salida</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showTransferModal && $selectedStockItem)
        <div class="fixed inset-0 z-50 flex justify-center overflow-y-auto bg-slate-950/60 px-4 py-8 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-3xl border bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex justify-between">
                    <h2 class="text-lg font-semibold dark:text-white">Transferir a otra obra</h2>
                    <button wire:click="closeTransferModal" type="button">Cerrar</button>
                </div>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $selectedStockItem->description }} — Origen: {{ $selectedStockItem->project?->code }} — Stock: {{ $selectedStockItem->stock_quantity }}</p>
                <div class="mt-4 grid gap-3">
                    <select wire:model="transfer_destination_project_id" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                        <option value="">Obra destino</option>
                        @foreach ($projects as $project)
                            @if ($project->id !== $selectedStockItem->work_project_id)
                                <option value="{{ $project->id }}">{{ $project->code }} — {{ $project->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <input wire:model="transfer_quantity" type="number" step="0.001" min="0.001" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Cantidad" />
                    <input wire:model="transfer_date" type="date" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" />
                    <textarea wire:model="transfer_reference" rows="2" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Motivo / referencia"></textarea>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="closeTransferModal" type="button" class="rounded-xl border px-4 py-2 text-sm">Cancelar</button>
                    <button wire:click="saveTransfer" type="button" class="rounded-xl bg-slate-950 px-4 py-2 text-sm text-white dark:bg-cyan-500 dark:text-slate-950">Transferir</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showKardexModal && $selectedStockItem)
        <div class="fixed inset-0 z-50 flex justify-center overflow-y-auto bg-slate-950/60 px-4 py-8 backdrop-blur-sm">
            <div class="w-full max-w-3xl rounded-3xl border bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex justify-between">
                    <div>
                        <h2 class="text-lg font-semibold dark:text-white">Kardex del item</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300">{{ $selectedStockItem->description }} — {{ $selectedStockItem->project?->code }}</p>
                    </div>
                    <button wire:click="closeKardexModal" type="button">Cerrar</button>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-xs uppercase text-slate-500 dark:border-slate-700">
                                <th class="py-2 pe-3">Fecha</th>
                                <th class="py-2 pe-3">Codigo</th>
                                <th class="py-2 pe-3">Origen</th>
                                <th class="py-2 pe-3">Dir.</th>
                                <th class="py-2 pe-3">Cant.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($selectedStockItem->movements as $movement)
                                <tr class="border-b dark:border-slate-800" wire:key="kardex-{{ $movement->id }}">
                                    <td class="py-2 pe-3">{{ $movement->movement_date?->format('d/m/Y') }}</td>
                                    <td class="py-2 pe-3">{{ $movement->movement_code }}</td>
                                    <td class="py-2 pe-3">{{ collect($movementSources)->first(fn ($s) => $s->value() === $movement->source)?->label() ?? $movement->source }}</td>
                                    <td class="py-2 pe-3">{{ $movement->direction === 'entrada' ? 'Entrada' : 'Salida' }}</td>
                                    <td class="py-2 pe-3 tabular-nums">{{ $movement->quantity }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-4 text-center text-slate-500">Sin movimientos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
