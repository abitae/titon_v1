<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Contratos con proveedores</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Controla contratos derivados de ordenes de compra, adjunta firma y sigue monto y saldo pendiente.</p>
        </div>
        <a href="{{ route('purchases.orders') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Ver ordenes</a>
    </div>

    <x-platform.filter-bar>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Numero o tipo de contrato" />
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Estado</label>
            <select wire:model.live="statusFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($statusOptions as $statusOption)
                    <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </div>
    </x-platform.filter-bar>

    <x-platform.compact-table :headers="['Contrato', 'Obra', 'Proveedor', 'Monto', 'Estado', 'Acciones']">
        @forelse ($contracts as $contract)
            <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="contract-row-{{ $contract->id }}">
                <td class="px-2.5 py-1.5 font-medium text-slate-950 dark:text-white">{{ $contract->contract_number }}</td>
                <td class="px-2.5 py-1.5">{{ $contract->project?->name ?? 'Sin obra' }}</td>
                <td class="px-2.5 py-1.5">{{ $contract->supplier?->business_name ?? 'Sin proveedor' }}</td>
                <td class="px-2.5 py-1.5">{{ $contract->currency }} {{ number_format((float) $contract->total_amount, 2) }}</td>
                <td class="px-2.5 py-1.5"><x-platform.status-badge :value="$contract->status" /></td>
                <td class="px-2.5 py-1.5">
                    <x-platform.action-buttons :edit="'openDetailModal('.$contract->id.')'" />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No hay contratos registrados.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-3xl border border-slate-200 bg-white px-2.5 py-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $contracts->links() }}
    </div>

    <x-platform.modal :show="$showDetailModal" max-width="max-w-6xl">
        @if ($selectedContract)
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $selectedContract->contract_number }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $selectedContract->supplier?->business_name ?? 'Sin proveedor' }} · {{ $selectedContract->project?->name ?? 'Sin obra' }}</p>
                </div>
                <button type="button" wire:click="closeModal" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-[1.1fr,0.9fr]">
                <div class="space-y-4">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/30">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Numero</label>
                                <input wire:model="selectedContract.contract_number" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Tipo</label>
                                <input wire:model="selectedContract.contract_type" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Inicio</label>
                                <input wire:model="selectedContract.start_date" type="date" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Fin</label>
                                <input wire:model="selectedContract.end_date" type="date" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Monto total</label>
                                <input wire:model="selectedContract.total_amount" type="number" min="0" step="0.01" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Estado</label>
                                <select wire:model="selectedContract.status" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                    @foreach ($statusOptions as $statusOption)
                                        <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Condiciones de pago</label>
                                <textarea wire:model="selectedContract.payment_conditions" rows="2" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Penalidades</label>
                                <textarea wire:model="selectedContract.penalties" rows="2" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Garantias</label>
                                <textarea wire:model="selectedContract.guarantees" rows="2" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Observacion</label>
                                <textarea wire:model="selectedContract.observation" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                            </div>
                        </div>
                        <button type="button" wire:click="updateContract" class="mt-4 rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Guardar contrato</button>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Contrato firmado</h3>
                        <div class="mt-4 space-y-2">
                            @forelse ($selectedContract->getMedia('signed_contract') as $media)
                                <a href="{{ $media->getUrl() }}" target="_blank" class="block rounded-2xl border border-slate-200 px-2.5 py-1.5 text-sm font-medium text-cyan-700 dark:border-slate-800 dark:text-cyan-300">{{ $media->file_name }}</a>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400">No hay contrato firmado adjunto.</p>
                            @endforelse
                        </div>
                        <input wire:model="signed_contract_files" type="file" multiple class="mt-4 block w-full rounded-xl border border-dashed border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('signed_contract_files.*') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        <button type="button" wire:click="uploadSignedContract" class="mt-4 rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Adjuntar firmado</button>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Indicadores</h3>
                        <div class="mt-4 space-y-3 text-sm text-slate-700 dark:text-slate-200">
                            <p><span class="font-medium">Monto contratado:</span> {{ $selectedContract->currency }} {{ number_format((float) $selectedContract->total_amount, 2) }}</p>
                            <p><span class="font-medium">Saldo pendiente:</span> {{ $selectedContract->currency }} {{ number_format($selectedContract->pendingBalance(), 2) }}</p>
                            <p><span class="font-medium">Pagos relacionados:</span> {{ $selectedContract->relatedPaymentsCount() }}</p>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Aprobacion</h3>
                        <textarea wire:model="approval_notes" rows="3" class="mt-4 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Notas de aprobacion"></textarea>
                        <button type="button" wire:click="approveContract" class="mt-4 w-full rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Aprobar contrato</button>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Anulacion</h3>
                        <textarea wire:model="cancellation_reason" rows="3" class="mt-4 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Motivo de anulacion"></textarea>
                        @error('cancellation_reason') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        <button type="button" wire:click="cancelContract" class="mt-4 w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300">Anular contrato</button>
                    </div>
                </div>
            </div>
        @endif
    </x-platform.modal>

    <x-platform.pdf-viewer-modal
        :show="$showPdfModal"
        :url="$pdfViewerUrl"
        :title="$pdfViewerTitle"
        :subtitle="$pdfViewerSubtitle"
        :allow-external-open="false"
    />
</div>
