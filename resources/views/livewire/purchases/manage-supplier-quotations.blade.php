<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Cotizaciones por solicitud</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $purchaseRequest->code }} · {{ $purchaseRequest->project?->name ?? 'Sin obra' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('modules.purchases') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Solicitudes</a>
            <flux:button type="button" variant="outline" wire:click="openComparisonModal">
                Comparativa
                @if (count($comparison_quotation_ids) > 0)
                    <span class="ms-1 tabular-nums">({{ count($comparison_quotation_ids) }})</span>
                @endif
            </flux:button>
            @can('purchases.aprobar')
                <flux:button type="button" variant="outline" wire:click="openWinnerModal">Seleccionar ganador</flux:button>
            @endcan
            <button type="button" wire:click="openCreateModal" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Nueva cotizacion</button>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-[1.1fr,0.9fr]">
        <div class="rounded-xl border border-slate-200 bg-white p-2.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Ítems solicitados</h2>
            <x-platform.compact-table
                dense
                :headers="['Producto', 'Und.', 'Cant.', 'CC UA', 'Especificación']"
                class="mt-2"
            >
                @forelse ($purchaseRequest->items as $item)
                    <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="requested-item-{{ $item->id }}">
                        <td class="max-w-[11rem] px-2.5 py-1.5">
                            <p class="truncate font-medium text-slate-950 dark:text-white">{{ $item->description }}</p>
                        </td>
                        <td class="whitespace-nowrap px-2.5 py-1.5">{{ $item->unit }}</td>
                        <td class="whitespace-nowrap px-2.5 py-1.5 text-end tabular-nums">{{ number_format((float) $item->quantity, 2) }}</td>
                        <td class="max-w-[7rem] truncate px-2.5 py-1.5">{{ $item->cost_center_ua ?: '—' }}</td>
                        <td class="max-w-[14rem] px-2.5 py-1.5">
                            <p class="line-clamp-2 text-[11px] leading-tight text-slate-500 dark:text-slate-400">
                                {{ $item->technical_specification ?: '—' }}
                            </p>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-2.5 py-4 text-center text-[11px] text-slate-500 dark:text-slate-400">
                            Esta solicitud no tiene ítems registrados.
                        </td>
                    </tr>
                @endforelse
            </x-platform.compact-table>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Resumen</h2>
            <div class="mt-2 space-y-2 text-xs text-slate-700 dark:text-slate-200">
                <p><span class="font-medium text-slate-500 dark:text-slate-400">Estado:</span> <x-platform.status-badge :value="$purchaseRequest->status" size="xs" /></p>
                <p><span class="font-medium text-slate-500 dark:text-slate-400">Descripción:</span> {{ $purchaseRequest->description ?: 'Sin descripción' }}</p>
                <p><span class="font-medium text-slate-500 dark:text-slate-400">Cotizaciones:</span> <span class="tabular-nums">{{ $quotations->count() }}</span></p>
            </div>
        </div>
    </div>

    <p class="text-[11px] text-slate-500 dark:text-slate-400">Marque al menos 2 cotizaciones para abrir la comparativa lado a lado.</p>

    <x-platform.compact-table dense :headers="['', 'Proveedor', 'Código', 'Origen', 'Vigencia', 'Total', 'Entrega', '']">
        @forelse ($quotations as $quotation)
            <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="quotation-row-{{ $quotation->id }}">
                <td class="w-8 px-2 py-1.5">
                    <input
                        type="checkbox"
                        wire:model.live="comparison_quotation_ids"
                        value="{{ $quotation->id }}"
                        class="size-3.5 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500 dark:border-slate-600 dark:bg-slate-950"
                        aria-label="Incluir {{ $quotation->supplier?->business_name ?? 'cotización' }} en la comparativa"
                    />
                </td>
                <td class="px-2.5 py-1.5">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $quotation->supplier?->business_name ?? 'Sin proveedor' }}</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $quotation->currency }}</p>
                </td>
                <td class="whitespace-nowrap px-2.5 py-1.5">{{ $quotation->code }}</td>
                <td class="whitespace-nowrap px-2.5 py-1.5">
                    @if ($quotation->isPdfCapture())
                        <div class="flex items-center gap-1">
                            <span class="rounded-full bg-rose-100 px-1.5 py-0.5 text-[10px] font-medium text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">PDF</span>
                        </div>
                    @else
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Formulario</span>
                    @endif
                </td>
                <td class="whitespace-nowrap px-2.5 py-1.5">{{ $quotation->valid_until?->format('d/m/y') ?? '—' }}</td>
                <td class="whitespace-nowrap px-2.5 py-1.5 tabular-nums">{{ $quotation->currency }} {{ number_format((float) $quotation->total, 2) }}</td>
                <td class="whitespace-nowrap px-2.5 py-1.5 tabular-nums">{{ $quotation->delivery_time_days }} d</td>
                <td class="whitespace-nowrap px-1.5 py-1">
                    <x-platform.action-buttons
                        :edit="'openEditModal('.$quotation->id.')'"
                        :delete="'deleteQuotation('.$quotation->id.')'"
                        delete-confirm="¿Eliminar esta cotización?"
                    />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-3 py-6 text-center text-xs text-slate-500 dark:text-slate-400">Todavía no hay cotizaciones registradas para esta solicitud.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <x-platform.modal compact :show="$showFormModal" max-width="max-w-4xl">
        <div class="flex items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ $editingQuotationId ? 'Editar cotización' : 'Nueva cotización' }}</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Complete el formulario o adjunte el PDF del proveedor.</p>
            </div>
            <flux:tooltip content="Cerrar">
                <flux:button type="button" variant="ghost" size="sm" icon="x-mark" wire:click="closeModal" class="!size-7 !min-h-0 !p-0" aria-label="Cerrar" />
            </flux:tooltip>
        </div>

        @if ($errors->any())
            <div class="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-2 py-1 dark:border-rose-900/40 dark:bg-rose-950/30">
                <p class="text-xs font-semibold text-rose-700 dark:text-rose-300">Revise los campos indicados:</p>
                <ul class="mt-1 list-inside list-disc text-[11px] text-rose-600 dark:text-rose-400">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-3 flex flex-wrap gap-1 rounded-lg border border-slate-200 bg-slate-50 p-1 dark:border-slate-800 dark:bg-slate-950/40">
            @foreach ($captureModeOptions as $captureModeOption)
                <flux:button
                    type="button"
                    wire:click="$set('capture_mode', '{{ $captureModeOption->value() }}')"
                    size="sm"
                    @class([
                        'rounded-md' => true,
                        '!bg-white !text-cyan-800 shadow-sm ring-1 ring-slate-200/90 dark:!bg-slate-800 dark:!text-cyan-300 dark:ring-slate-700' => $capture_mode === $captureModeOption->value(),
                        '!text-slate-600 hover:!bg-white/80 dark:!text-slate-400 dark:hover:!bg-slate-800/60' => $capture_mode !== $captureModeOption->value(),
                    ])
                >
                    {{ $captureModeOption->label() }}
                </flux:button>
            @endforeach
        </div>

        <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2">
                <x-platform.searchable-select
                    label="Proveedor"
                    :options="$supplierOptions"
                    option-label="business_name"
                    option-secondary="ruc"
                    search-model="supplier_search"
                    :selected-value="$supplier_id"
                    select-method="selectSupplier"
                    placeholder="Buscar por razón social o RUC..."
                    :error="$errors->first('supplier_id')"
                />
            </div>
            @if ($editingQuotationId)
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Código</label>
                    <input wire:model="code" readonly class="mt-1 block h-8 w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300" />
                </div>
            @else
                <div class="flex items-center rounded-lg border border-dashed border-cyan-300 bg-cyan-50 px-2.5 py-1.5 text-[11px] leading-tight text-cyan-900 dark:border-cyan-800 dark:bg-cyan-950/40 dark:text-cyan-200 lg:col-span-2">
                    Código automático al guardar.
                </div>
            @endif
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fecha</label>
                <input wire:model="quotation_date" type="date" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('quotation_date') border-rose-500 @enderror" />
                @error('quotation_date') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Vigencia</label>
                <input wire:model="valid_until" type="date" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('valid_until') border-rose-500 @enderror" />
                @error('valid_until') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Moneda</label>
                <select wire:model="currency" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('currency') border-rose-500 @enderror">
                    @foreach ($currencyOptions as $currencyOption)
                        <option value="{{ $currencyOption->value() }}">{{ $currencyOption->label() }}</option>
                    @endforeach
                </select>
                @error('currency') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            @if ($capture_mode === 'pdf')
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Subtotal</label>
                    <input wire:model="subtotal" type="number" min="0" step="0.01" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('subtotal') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
            @endif
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">IGV / impuesto</label>
                <input wire:model="tax" type="number" min="0" step="0.01" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('tax') border-rose-500 @enderror" />
                @error('tax') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Entrega (días)</label>
                <input wire:model="delivery_time" type="number" min="0" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('delivery_time') border-rose-500 @enderror" />
                @error('delivery_time') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Condiciones de pago</label>
                <textarea wire:model="payment_conditions" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Garantía</label>
                <textarea wire:model="warranty" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
            <div class="sm:col-span-2 lg:col-span-4">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Observación</label>
                <textarea wire:model="observation" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
        </div>

        @if ($capture_mode === 'pdf')
            <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-2.5 dark:border-slate-800 dark:bg-slate-950/40">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Archivo PDF de cotización</label>
                <input wire:model="quotation_pdf" type="file" accept="application/pdf,.pdf" class="mt-1 block w-full text-xs text-slate-600 file:mr-2 file:rounded-md file:border-0 file:bg-slate-900 file:px-2.5 file:py-1.5 file:text-xs file:font-medium file:text-white dark:text-slate-300 dark:file:bg-cyan-600 dark:file:text-slate-950" />
                @error('quotation_pdf') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                <div wire:loading wire:target="quotation_pdf" class="mt-1 text-[11px] text-slate-500">Subiendo archivo…</div>
                @if ($editingQuotationId)
                    <div class="mt-2 flex items-center gap-2">
                        <span class="text-[11px] text-slate-500 dark:text-slate-400">Vista previa:</span>
                        <flux:button type="button" variant="outline" size="sm" icon="document" wire:click="openPdfModal({{ $editingQuotationId }})">Ver PDF</flux:button>
                    </div>
                @endif
                <p class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400">Ingrese subtotal e impuesto según el PDF. El detalle de ítems queda en el documento adjunto.</p>
            </div>
        @else
            <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-2.5 dark:border-slate-800 dark:bg-slate-950/40">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Ítems cotizados</h3>
                        <span class="text-[11px] tabular-nums text-slate-500 dark:text-slate-400">{{ count($items) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($editingQuotationId)
                            <flux:button type="button" variant="outline" icon="document" wire:click="openPdfModal({{ $editingQuotationId }})" size="sm">Ver PDF</flux:button>
                        @endif
                        <flux:button type="button" variant="outline" icon="plus" wire:click="openItemModal" size="sm">Agregar</flux:button>
                    </div>
                </div>

                @error('items')
                    <p class="mt-1.5 text-[11px] text-rose-600">{{ $message }}</p>
                @enderror
                @foreach ($errors->getMessages() as $field => $messages)
                    @if (str_starts_with($field, 'items.'))
                        @foreach ($messages as $message)
                            <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                        @endforeach
                    @endif
                @endforeach

                <x-platform.compact-table
                    dense
                    :headers="['Producto', 'Und.', 'Cant.', 'P. unit.', 'Total', '']"
                    class="mt-2"
                >
                    @forelse ($items as $index => $item)
                        <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="quotation-item-{{ $index }}">
                            <td class="max-w-[11rem] px-2.5 py-1.5">
                                <p class="truncate font-medium text-slate-950 dark:text-white">{{ $item['product_or_service'] }}</p>
                            </td>
                            <td class="whitespace-nowrap px-2.5 py-1.5">{{ $item['unit'] }}</td>
                            <td class="whitespace-nowrap px-2.5 py-1.5 text-end tabular-nums">{{ number_format((float) $item['quantity'], 2) }}</td>
                            <td class="whitespace-nowrap px-2.5 py-1.5 text-end tabular-nums">{{ number_format((float) $item['unit_price'], 2) }}</td>
                            <td class="whitespace-nowrap px-2.5 py-1.5 text-end tabular-nums font-medium text-slate-950 dark:text-white">
                                {{ number_format((float) $item['quantity'] * (float) $item['unit_price'], 2) }}
                            </td>
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
                                            aria-label="Editar ítem"
                                        />
                                    </flux:tooltip>
                                    <flux:tooltip content="Quitar">
                                        <flux:button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            icon="trash"
                                            wire:click="removeItem({{ $index }})"
                                            wire:confirm="¿Quitar este ítem?"
                                            class="!size-7 !min-h-0 !p-0 !text-rose-600 hover:!text-rose-700 dark:!text-rose-400 dark:hover:!text-rose-300"
                                            aria-label="Quitar ítem"
                                        />
                                    </flux:tooltip>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-2.5 py-4 text-center text-[11px] text-slate-500 dark:text-slate-400">
                                Sin ítems. Usa «Agregar» para comenzar.
                            </td>
                        </tr>
                    @endforelse
                </x-platform.compact-table>
            </div>
        @endif

        <div class="mt-3 flex items-center justify-end gap-2">
            <flux:button type="button" variant="outline" wire:click="closeModal" size="sm">Cancelar</flux:button>
            <flux:button type="button" variant="primary" wire:click="saveQuotation" size="sm">Guardar</flux:button>
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
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Precio unitario</label>
                        <input wire:model="item_unit_price" type="number" step="0.01" min="0" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('item_unit_price') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
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

    <x-platform.pdf-viewer-modal
        :show="$showPdfModal"
        :url="$pdfViewerUrl"
        :title="$pdfViewerTitle"
        subtitle="Vista previa del PDF de cotización"
    />

    @include('livewire.purchases.partials.select-winner-modal', [
        'summary' => $summary,
        'comparison' => $comparison,
    ])

    @include('livewire.purchases.partials.quotation-comparison-fullscreen-modal', [
        'summary' => $comparisonSummary,
    ])
</div>
