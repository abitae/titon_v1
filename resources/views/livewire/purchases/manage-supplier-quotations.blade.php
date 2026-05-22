<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Cotizaciones por solicitud</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $purchaseRequest->code }} · {{ $purchaseRequest->project?->name ?? 'Sin obra' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('modules.purchases') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Solicitudes</a>
            <a href="{{ route('purchases.comparison', $purchaseRequest) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Comparativa</a>
            <button type="button" wire:click="openCreateModal" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Nueva cotizacion</button>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-[1.1fr,0.9fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Items solicitados</h2>
            <div class="mt-4 space-y-3">
                @foreach ($purchaseRequest->items as $item)
                    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                        <p class="font-medium text-slate-950 dark:text-white">{{ $item->product_or_service }}</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $item->quantity }} {{ $item->unit }}</p>
                        <p class="mt-2 text-sm text-slate-700 dark:text-slate-200">{{ $item->technical_specification ?: 'Sin especificacion tecnica.' }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Resumen</h2>
            <div class="mt-4 space-y-3 text-sm text-slate-700 dark:text-slate-200">
                <p><span class="font-medium">Estado:</span> <x-platform.status-badge :value="$purchaseRequest->status" /></p>
                <p><span class="font-medium">Descripcion:</span> {{ $purchaseRequest->description ?: 'Sin descripcion' }}</p>
                <p><span class="font-medium">Cotizaciones registradas:</span> {{ $quotations->count() }}</p>
            </div>
        </div>
    </div>

    <x-platform.compact-table :headers="['Proveedor', 'Codigo', 'Vigencia', 'Total', 'Entrega', 'Acciones']">
        @forelse ($quotations as $quotation)
            <tr class="text-sm text-slate-700 dark:text-slate-200" wire:key="quotation-row-{{ $quotation->id }}">
                <td class="px-6 py-4">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $quotation->supplier?->business_name ?? 'Sin proveedor' }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ $quotation->currency }}</p>
                </td>
                <td class="px-6 py-4">{{ $quotation->code }}</td>
                <td class="px-6 py-4">{{ $quotation->valid_until?->format('d/m/Y') ?? 'Sin vigencia' }}</td>
                <td class="px-6 py-4">{{ $quotation->currency }} {{ number_format((float) $quotation->total, 2) }}</td>
                <td class="px-6 py-4">{{ $quotation->delivery_time }} dias</td>
                <td class="px-6 py-4">
                    <div class="flex flex-wrap gap-2 justify-end">
                        <button type="button" wire:click="openEditModal({{ $quotation->id }})" class="rounded-lg px-2 py-1 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200">Editar</button>
                        <button type="button" wire:click="deleteQuotation({{ $quotation->id }})" wire:confirm="¿Eliminar esta cotizacion?" class="rounded-lg px-2 py-1 text-sm font-medium text-rose-700 hover:bg-rose-50 dark:text-rose-300">Eliminar</button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Todavia no hay cotizaciones registradas para esta solicitud.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <x-platform.modal :show="$showFormModal" max-width="max-w-6xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $editingQuotationId ? 'Editar cotizacion' : 'Nueva cotizacion' }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Captura la oferta del proveedor y compara tiempos, montos y condiciones.</p>
            </div>
            <button type="button" wire:click="closeModal" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Proveedor</label>
                <select wire:model="supplier_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->business_name }}</option>
                    @endforeach
                </select>
                @error('supplier_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            @if ($editingQuotationId)
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Codigo</label>
                    <input wire:model="code" readonly class="mt-2 block w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300" />
                </div>
            @else
                <div class="rounded-xl border border-dashed border-cyan-300 bg-cyan-50 px-4 py-3 text-sm text-cyan-900 dark:border-cyan-800 dark:bg-cyan-950/40 dark:text-cyan-200 md:col-span-2">
                    El codigo de cotizacion se generara automaticamente al guardar.
                </div>
            @endif
            <div class="grid gap-4 sm:grid-cols-2 md:col-span-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Fecha de cotizacion</label>
                    <input wire:model="quotation_date" type="date" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Vigencia</label>
                    <input wire:model="valid_until" type="date" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-3 md:col-span-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Moneda</label>
                    <select wire:model="currency" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        @foreach ($currencyOptions as $currencyOption)
                            <option value="{{ $currencyOption->value() }}">{{ $currencyOption->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">IGV / impuesto</label>
                    <input wire:model="tax" type="number" min="0" step="0.01" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Entrega (dias)</label>
                    <input wire:model="delivery_time" type="number" min="0" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Condiciones de pago</label>
                <textarea wire:model="payment_conditions" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Garantia</label>
                <textarea wire:model="warranty" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Observacion</label>
                <textarea wire:model="observation" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
        </div>

        <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/40">
            <div class="flex items-center justify-between gap-4">
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Items cotizados</h3>
                <button type="button" wire:click="addItem" class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Agregar item</button>
            </div>
            <div class="mt-4 space-y-4">
                @foreach ($items as $index => $item)
                    <div class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900" wire:key="quotation-item-{{ $index }}">
                        <div class="grid gap-4 md:grid-cols-[2fr,0.7fr,0.7fr,0.8fr,auto]">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Producto o servicio</label>
                                <input wire:model="items.{{ $index }}.product_or_service" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Unidad</label>
                                <input wire:model="items.{{ $index }}.unit" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Cantidad</label>
                                <input wire:model="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Precio unitario</label>
                                <input wire:model="items.{{ $index }}.unit_price" type="number" step="0.01" min="0" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            </div>
                            <div class="flex items-end">
                                <button type="button" wire:click="removeItem({{ $index }})" class="rounded-xl border border-rose-200 px-3 py-2 text-sm font-medium text-rose-700 dark:border-rose-900/40 dark:text-rose-300">Quitar</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" wire:click="closeModal" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancelar</button>
            <button type="button" wire:click="saveQuotation" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Guardar cotizacion</button>
        </div>
    </x-platform.modal>
</div>
