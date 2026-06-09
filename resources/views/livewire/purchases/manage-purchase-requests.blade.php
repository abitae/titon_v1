<div class="space-y-6">
    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Solicitudes registradas</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">En borrador</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($summary['draft']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">En proceso</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($summary['in_process']) }}</p>
        </div>
    </section>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Requerimientos</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Gestiona requerimientos por obra, define prioridad y deja la base lista para cotizar.</p>
        </div>
        <flux:button type="button" variant="primary" icon="plus" wire:click="openCreateModal" size="sm">
            Nueva solicitud
        </flux:button>
    </div>

    <x-platform.filter-bar>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Codigo o descripcion" />
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
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Obra</label>
            <select wire:model.live="projectFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
    </x-platform.filter-bar>

    <x-platform.compact-table :headers="['Codigo', 'Obra', 'Solicitante', 'Estado', 'Items', 'Cotizaciones', 'Acciones']">
        @forelse ($purchaseRequests as $purchaseRequest)
            <tr class="text-sm text-slate-700 dark:text-slate-200" wire:key="purchase-request-{{ $purchaseRequest->id }}">
                <td class="px-6 py-4">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $purchaseRequest->code }}</p>
                    @if ($purchaseRequest->costType)
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $purchaseRequest->costType->name }}</p>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $purchaseRequest->project?->name ?? 'Sin obra' }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ $purchaseRequest->request_date?->format('d/m/Y') }}</p>
                </td>
                <td class="px-6 py-4">
                    <p>{{ $purchaseRequest->requester?->name ?? 'Sin usuario' }}</p>
                    <div class="mt-2"><x-platform.status-badge :value="$purchaseRequest->priority" /></div>
                </td>
                <td class="px-6 py-4"><x-platform.status-badge :value="$purchaseRequest->status" /></td>
                <td class="px-6 py-4 text-center tabular-nums">{{ $purchaseRequest->items_count }}</td>
                <td class="px-6 py-4 text-center tabular-nums">{{ $purchaseRequest->quotations_count }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-1">
                        <flux:tooltip content="Enviar a proveedores">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="paper-airplane"
                                href="{{ route('purchases.send-suppliers', $purchaseRequest) }}"
                                wire:navigate
                                class="!text-emerald-600 hover:!text-emerald-700 dark:!text-emerald-400 dark:hover:!text-emerald-300"
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
                                class="!text-cyan-700 hover:!text-cyan-800 dark:!text-cyan-300 dark:hover:!text-cyan-200"
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
                                class="!text-rose-600 hover:!text-rose-700 dark:!text-rose-400 dark:hover:!text-rose-300"
                                aria-label="Eliminar solicitud"
                            />
                        </flux:tooltip>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No hay solicitudes registradas para la empresa activa.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-3xl border border-slate-200 bg-white px-6 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $purchaseRequests->links() }}
    </div>

    <x-platform.modal :show="$showFormModal" max-width="max-w-6xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $editingPurchaseRequestId ? 'Editar solicitud' : 'Nueva solicitud de compra' }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Registra el requerimiento y detalla los items con especificaciones tecnicas.</p>
            </div>
            <button type="button" wire:click="closeModal" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            @if ($editingPurchaseRequestId)
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Codigo</label>
                    <input wire:model="code" readonly class="mt-2 block w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300" />
                </div>
            @else
                <div class="rounded-xl border border-dashed border-cyan-300 bg-cyan-50 px-4 py-3 text-sm text-cyan-900 dark:border-cyan-800 dark:bg-cyan-950/40 dark:text-cyan-200">
                    El codigo del requerimiento se generara automaticamente al guardar.
                </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Obra</label>
                <select wire:model="work_project_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
                @error('work_project_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Solicitante</label>
                <select wire:model="requested_by" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('requested_by') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Prioridad</label>
                    <select wire:model="priority" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        @foreach ($priorityOptions as $priorityOption)
                            <option value="{{ $priorityOption->value() }}">{{ $priorityOption->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Fecha</label>
                    <input wire:model="request_date" type="date" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Descripcion</label>
                <textarea wire:model="description" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Tipo de costo</label>
                <select wire:model="cost_type_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($costTypes as $costType)
                        <option value="{{ $costType->id }}">{{ $costType->name }}</option>
                    @endforeach
                </select>
                @error('cost_type_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Estado</label>
                <select wire:model="status" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/40">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Items solicitados</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ count($items) }} item(s) agregado(s)</p>
                </div>
                <button type="button" wire:click="openItemModal" class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-white dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900">Agregar item</button>
            </div>

            @error('items')
                <p class="mt-3 text-sm text-rose-600">{{ $message }}</p>
            @enderror

            <x-platform.compact-table
                :headers="['Producto o servicio', 'Unidad', 'Cantidad', 'Centro de costo UA', 'Acciones']"
                class="mt-4 rounded-2xl"
            >
                @forelse ($items as $index => $item)
                    <tr class="text-sm text-slate-700 dark:text-slate-200" wire:key="request-item-{{ $index }}">
                        <td class="px-4 py-3 sm:px-6">
                            <p class="font-medium text-slate-950 dark:text-white">{{ $item['product_or_service'] }}</p>
                        </td>
                        <td class="px-4 py-3 sm:px-6">{{ $item['unit'] }}</td>
                        <td class="px-4 py-3 text-end tabular-nums sm:px-6">{{ number_format((float) $item['quantity'], 2) }}</td>
                        <td class="px-4 py-3 sm:px-6">{{ ($item['cost_center_ua'] ?? '') ?: '—' }}</td>
                        <td class="px-4 py-3 sm:px-6">
                            <div class="flex items-center justify-end gap-1">
                                <flux:tooltip content="Editar">
                                    <flux:button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil-square"
                                        wire:click="openItemModal({{ $index }})"
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
                                        class="!text-rose-600 hover:!text-rose-700 dark:!text-rose-400 dark:hover:!text-rose-300"
                                        aria-label="Quitar item"
                                    />
                                </flux:tooltip>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                            No hay items agregados. Usa "Agregar item" para comenzar.
                        </td>
                    </tr>
                @endforelse
            </x-platform.compact-table>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" wire:click="closeModal" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancelar</button>
            <button type="button" wire:click="savePurchaseRequest" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Guardar solicitud</button>
        </div>

        <x-slot:stacked>
            <x-platform.stacked-modal :show="$showItemModal" max-width="max-w-2xl">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $editingItemIndex !== null ? 'Editar item' : 'Nuevo item' }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Completa los datos del producto o servicio solicitado.</p>
                </div>
                <button type="button" wire:click="closeItemModal" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Producto o servicio</label>
                    <input wire:model="item_product_or_service" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('item_product_or_service') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Unidad</label>
                    <input wire:model="item_unit" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('item_unit') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Cantidad</label>
                    <input wire:model="item_quantity" type="number" step="0.01" min="0.01" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('item_quantity') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Centro de costo UA</label>
                    <input wire:model="item_cost_center_ua" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Centro de costo UA" />
                    @error('item_cost_center_ua') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Especificacion tecnica</label>
                    <textarea wire:model="item_technical_specification" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                    @error('item_technical_specification') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Observacion</label>
                    <textarea wire:model="item_observation" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                    @error('item_observation') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" wire:click="closeItemModal" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancelar</button>
                <button type="button" wire:click="saveItem" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400">
                    {{ $editingItemIndex !== null ? 'Actualizar item' : 'Agregar item' }}
                </button>
            </div>
        </x-platform.stacked-modal>
        </x-slot>
    </x-platform.modal>
</div>
