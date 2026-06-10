<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-xs text-slate-600 dark:text-slate-300">
            <span><span class="text-slate-500 dark:text-slate-400">Total</span> <strong class="tabular-nums text-slate-950 dark:text-white">{{ number_format($summary['total']) }}</strong></span>
            <span><span class="text-slate-500 dark:text-slate-400">Borrador</span> <strong class="tabular-nums text-slate-950 dark:text-white">{{ number_format($summary['draft']) }}</strong></span>
            <span><span class="text-slate-500 dark:text-slate-400">En proceso</span> <strong class="tabular-nums text-slate-950 dark:text-white">{{ number_format($summary['in_process']) }}</strong></span>
        </div>
        <flux:button type="button" variant="primary" icon="plus" wire:click="openCreateModal" size="sm">
            Nueva solicitud
        </flux:button>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-950 dark:text-white">Requerimientos</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Gestiona requerimientos por obra y prepara la base para cotizar.</p>
        </div>
    </div>

    <div class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="min-w-[10rem] flex-1">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Código o descripción" />
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
        <div class="min-w-[9rem] flex-1 sm:max-w-xs">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Obra</label>
            <select wire:model.live="projectFilter" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <x-platform.compact-table dense :headers="['Requerimiento', 'Obra / Solicitante', 'Estado', 'It.', 'Cot.', '']">
        @forelse ($purchaseRequests as $purchaseRequest)
            <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="purchase-request-{{ $purchaseRequest->id }}">
                <td class="whitespace-nowrap px-2.5 py-1.5">
                    <p class="font-semibold leading-tight text-slate-950 dark:text-white">{{ $purchaseRequest->code }}</p>
                    <p class="mt-0.5 truncate text-[10px] leading-tight text-slate-500 dark:text-slate-400">
                        {{ $purchaseRequest->costType?->name ?? 'Sin tipo' }}
                        · {{ $purchaseRequest->request_date?->format('d/m/y') ?? '—' }}
                    </p>
                </td>
                <td class="max-w-[14rem] px-2.5 py-1.5">
                    <p class="truncate font-medium leading-tight text-slate-950 dark:text-white">{{ $purchaseRequest->project?->name ?? 'Sin obra' }}</p>
                    <div class="mt-0.5 flex flex-wrap items-center gap-1.5">
                        <span class="truncate text-[10px] text-slate-500 dark:text-slate-400">{{ $purchaseRequest->requester?->name ?? 'Sin usuario' }}</span>
                        <x-platform.status-badge :value="$purchaseRequest->priority" size="xs" />
                    </div>
                </td>
                <td class="whitespace-nowrap px-2.5 py-1.5">
                    <x-platform.status-badge :value="$purchaseRequest->status" size="xs" />
                </td>
                <td class="px-2.5 py-1.5 text-center tabular-nums">{{ $purchaseRequest->items_count }}</td>
                <td class="px-2.5 py-1.5 text-center tabular-nums">{{ $purchaseRequest->quotations_count }}</td>
                <td class="whitespace-nowrap px-1.5 py-1">
                    <div class="flex items-center justify-end gap-0">
                        <flux:tooltip content="Enviar a proveedores">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="paper-airplane"
                                href="{{ route('purchases.send-suppliers', $purchaseRequest) }}"
                                wire:navigate
                                class="!size-7 !min-h-0 !p-0 !text-emerald-600 hover:!text-emerald-700 dark:!text-emerald-400 dark:hover:!text-emerald-300"
                                aria-label="Enviar a proveedores"
                            />
                        </flux:tooltip>
                        <flux:tooltip content="Cotizaciones">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="document-text"
                                href="{{ route('purchases.quotations', $purchaseRequest) }}"
                                wire:navigate
                                class="!size-7 !min-h-0 !p-0 !text-cyan-700 hover:!text-cyan-800 dark:!text-cyan-300 dark:hover:!text-cyan-200"
                                aria-label="Cotizaciones"
                            />
                        </flux:tooltip>
                        <flux:tooltip content="Comparar">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="scale"
                                href="{{ route('purchases.comparison', $purchaseRequest) }}"
                                wire:navigate
                                class="!size-7 !min-h-0 !p-0"
                                aria-label="Comparar cotizaciones"
                            />
                        </flux:tooltip>
                        <flux:tooltip content="Editar">
                            <flux:button
                                type="button"
                                variant="ghost"
                                size="sm"
                                icon="pencil-square"
                                wire:click="openEditModal({{ $purchaseRequest->id }})"
                                class="!size-7 !min-h-0 !p-0"
                                aria-label="Editar solicitud"
                            />
                        </flux:tooltip>
                        <flux:tooltip content="Eliminar">
                            <flux:button
                                type="button"
                                variant="ghost"
                                size="sm"
                                icon="trash"
                                wire:click="deletePurchaseRequest({{ $purchaseRequest->id }})"
                                wire:confirm="¿Eliminar esta solicitud?"
                                class="!size-7 !min-h-0 !p-0 !text-rose-600 hover:!text-rose-700 dark:!text-rose-400 dark:hover:!text-rose-300"
                                aria-label="Eliminar solicitud"
                            />
                        </flux:tooltip>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-3 py-6 text-center text-xs text-slate-500 dark:text-slate-400">No hay solicitudes registradas para la empresa activa.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $purchaseRequests->links() }}
    </div>

    <x-platform.modal compact :show="$showFormModal" max-width="max-w-4xl">
        <div class="flex items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ $editingPurchaseRequestId ? 'Editar solicitud' : 'Nueva solicitud' }}</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Datos del requerimiento e ítems solicitados.</p>
            </div>
            <flux:tooltip content="Cerrar">
                <flux:button type="button" variant="ghost" size="sm" icon="x-mark" wire:click="closeModal" class="!size-7 !min-h-0 !p-0" aria-label="Cerrar" />
            </flux:tooltip>
        </div>

        <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            @if ($editingPurchaseRequestId)
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Código</label>
                    <input wire:model="code" readonly class="mt-1 block h-8 w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300" />
                </div>
            @else
                <div class="flex items-center rounded-lg border border-dashed border-cyan-300 bg-cyan-50 px-2.5 py-1.5 text-[11px] leading-tight text-cyan-900 dark:border-cyan-800 dark:bg-cyan-950/40 dark:text-cyan-200 sm:col-span-2 lg:col-span-1">
                    Código automático al guardar.
                </div>
            @endif
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Obra</label>
                <select wire:model="work_project_id" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
                @error('work_project_id') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Solicitante</label>
                <select wire:model="requested_by" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('requested_by') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Prioridad</label>
                <select wire:model="priority" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @foreach ($priorityOptions as $priorityOption)
                        <option value="{{ $priorityOption->value() }}">{{ $priorityOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fecha</label>
                <input wire:model="request_date" type="date" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tipo de costo</label>
                <select wire:model="cost_type_id" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($costTypes as $costType)
                        <option value="{{ $costType->id }}">{{ $costType->name }}</option>
                    @endforeach
                </select>
                @error('cost_type_id') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</label>
                <select wire:model="status" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-4">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Descripción</label>
                <textarea wire:model="description" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
        </div>

        <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-2.5 dark:border-slate-800 dark:bg-slate-950/40">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-baseline gap-2">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Ítems</h3>
                    <span class="text-[11px] tabular-nums text-slate-500 dark:text-slate-400">{{ count($items) }}</span>
                </div>
                <flux:button type="button" variant="outline" icon="plus" wire:click="openItemModal" size="sm">
                    Agregar
                </flux:button>
            </div>

            @error('items')
                <p class="mt-1.5 text-[11px] text-rose-600">{{ $message }}</p>
            @enderror

            <x-platform.compact-table
                dense
                :headers="['Producto', 'Und.', 'Cant.', 'CC UA', '']"
                class="mt-2"
            >
                @forelse ($items as $index => $item)
                    <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="request-item-{{ $index }}">
                        <td class="max-w-[12rem] px-2.5 py-1.5">
                            <p class="truncate font-medium text-slate-950 dark:text-white">{{ $item['product_or_service'] }}</p>
                        </td>
                        <td class="whitespace-nowrap px-2.5 py-1.5">{{ $item['unit'] }}</td>
                        <td class="whitespace-nowrap px-2.5 py-1.5 text-end tabular-nums">{{ number_format((float) $item['quantity'], 2) }}</td>
                        <td class="max-w-[8rem] truncate px-2.5 py-1.5">{{ ($item['cost_center_ua'] ?? '') ?: '—' }}</td>
                        <td class="whitespace-nowrap px-1 py-1">
                            <div class="flex items-center justify-end gap-0">
                                <flux:tooltip content="Editar">
                                    <flux:button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil-square"
                                        wire:click="openItemModal({{ $index }})"
                                        class="!size-7 !min-h-0 !p-0"
                                        aria-label="Editar item"
                                    />
                                </flux:tooltip>
                                <flux:tooltip content="Quitar">
                                    <flux:button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        wire:click="removeItem({{ $index }})"
                                        wire:confirm="¿Quitar este item?"
                                        class="!size-7 !min-h-0 !p-0 !text-rose-600 hover:!text-rose-700 dark:!text-rose-400 dark:hover:!text-rose-300"
                                        aria-label="Quitar item"
                                    />
                                </flux:tooltip>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-2.5 py-4 text-center text-[11px] text-slate-500 dark:text-slate-400">
                            Sin ítems. Usa «Agregar» para comenzar.
                        </td>
                    </tr>
                @endforelse
            </x-platform.compact-table>
        </div>

        <div class="mt-3 flex items-center justify-end gap-2">
            <flux:button type="button" variant="outline" wire:click="closeModal" size="sm">Cancelar</flux:button>
            <flux:button type="button" variant="primary" wire:click="savePurchaseRequest" size="sm">Guardar</flux:button>
        </div>

        <x-slot:stacked>
            <x-platform.stacked-modal compact :show="$showItemModal" max-width="max-w-lg">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ $editingItemIndex !== null ? 'Editar ítem' : 'Nuevo ítem' }}</h2>
                </div>
                <flux:tooltip content="Cerrar">
                    <flux:button type="button" variant="ghost" size="sm" icon="x-mark" wire:click="closeItemModal" class="!size-7 !min-h-0 !p-0" aria-label="Cerrar" />
                </flux:tooltip>
            </div>

            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Producto o servicio</label>
                    <input wire:model="item_product_or_service" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('item_product_or_service') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Unidad</label>
                    <input wire:model="item_unit" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('item_unit') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Cantidad</label>
                    <input wire:model="item_quantity" type="number" step="0.01" min="0.01" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('item_quantity') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Centro de costo UA</label>
                    <input wire:model="item_cost_center_ua" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Centro de costo UA" />
                    @error('item_cost_center_ua') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Especificación técnica</label>
                    <textarea wire:model="item_technical_specification" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                    @error('item_technical_specification') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Observación</label>
                    <textarea wire:model="item_observation" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                    @error('item_observation') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-3 flex items-center justify-end gap-2">
                <flux:button type="button" variant="outline" wire:click="closeItemModal" size="sm">Cancelar</flux:button>
                <flux:button type="button" variant="primary" wire:click="saveItem" size="sm">
                    {{ $editingItemIndex !== null ? 'Actualizar' : 'Agregar' }}
                </flux:button>
            </div>
        </x-platform.stacked-modal>
        </x-slot>
    </x-platform.modal>
</div>
