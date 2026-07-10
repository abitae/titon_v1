@php
    $actionLabels = [
        'receive' => 'Recibir',
        'process' => 'En proceso',
        'attend' => 'Atender',
        'derive' => 'Derivar',
        'observe' => 'Observar',
        'approve' => 'Aprobar',
        'reject' => 'Rechazar',
        'archive' => 'Archivar',
        'reopen' => 'Reabrir',
        'cancel' => 'Anular',
        'close' => 'Cerrar',
    ];
@endphp

<div class="space-y-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $document->subject }}</h1>
                <x-platform.status-badge :value="$document->status" size="xs" />
                <x-platform.status-badge :value="$document->priority" size="xs" />
            </div>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{ $document->code }} · {{ $document->document_number ?: 'Sin numero' }} · {{ $document->documentType?->name ?? 'Sin tipo' }} · {{ $document->project?->name ?? 'Sin obra' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button variant="outline" href="{{ route('modules.documents') }}" wire:navigate size="sm">Entrada</flux:button>
            <flux:button variant="outline" href="{{ route('documents.timeline', $document) }}" wire:navigate size="sm">Timeline</flux:button>
        </div>
    </div>

    <div class="grid gap-3 lg:grid-cols-[1.2fr,0.8fr]">
        <div class="space-y-3">
            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Resumen</h2>
                <div class="mt-2 grid gap-2 text-xs sm:grid-cols-2">
                    <div><span class="text-slate-500">Origen:</span> <span class="text-slate-900 dark:text-white">{{ $document->originArea?->name ?? '—' }}</span></div>
                    <div><span class="text-slate-500">Destino:</span> <span class="text-slate-900 dark:text-white">{{ $document->currentLocationLabel() }}</span></div>
                    <div><span class="text-slate-500">Responsable:</span> <span class="text-slate-900 dark:text-white">{{ $document->currentUser?->name ?? '—' }}</span></div>
                    <div><span class="text-slate-500">Registro:</span> <span class="text-slate-900 dark:text-white">{{ $document->createdByUser?->name ?? '—' }}</span></div>
                    <div><span class="text-slate-500">Emision:</span> <span class="text-slate-900 dark:text-white">{{ $document->issue_date?->format('d/m/Y') ?? '—' }}</span></div>
                    <div><span class="text-slate-500">Vencimiento:</span> <span class="text-slate-900 dark:text-white">{{ $document->due_date?->format('d/m/Y') ?? '—' }}</span></div>
                    <div class="sm:col-span-2"><span class="text-slate-500">Descripcion:</span> <span class="text-slate-700 dark:text-slate-200">{{ $document->description ?: 'Sin descripcion.' }}</span></div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Archivos</h2>
                    <span class="text-[11px] text-slate-500">{{ $document->getMedia('attachments')->count() }} adjuntos</span>
                </div>
                <div class="mt-2 flex flex-col gap-1.5">
                    @forelse ($document->getMedia('attachments') as $media)
                        <a href="{{ $media->getUrl() }}" target="_blank" class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-medium text-cyan-700 hover:bg-cyan-50 dark:border-slate-800 dark:text-cyan-300">{{ $media->file_name }}</a>
                    @empty
                        <p class="text-[11px] text-slate-500">Sin archivos adjuntos.</p>
                    @endforelse
                </div>
                @can('documents.editar')
                    <div class="mt-2 flex flex-wrap items-end gap-2">
                        <input wire:model="newAttachments" type="file" multiple class="min-w-0 flex-1 text-[11px] file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 dark:file:bg-slate-800" />
                        <flux:button type="button" variant="outline" size="sm" wire:click="uploadAttachments">Subir</flux:button>
                    </div>
                    @error('newAttachments.*') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                @endcan
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Timeline reciente</h2>
                <div class="mt-2 space-y-2">
                    @forelse ($timeline->take(5) as $entry)
                        <div class="rounded-lg border border-slate-200 px-2 py-1.5 dark:border-slate-800">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-medium text-slate-950 dark:text-white">{{ $entry['title'] }}</p>
                                <x-platform.status-badge :value="$entry['status']" size="xs" />
                            </div>
                            <p class="mt-0.5 text-[10px] text-slate-500">{{ $entry['actor'] ?? 'Sistema' }} · {{ optional($entry['created_at'])->format('d/m/Y H:i') }}</p>
                            <p class="mt-1 text-[11px] text-slate-700 dark:text-slate-200">{{ $entry['description'] }}</p>
                        </div>
                    @empty
                        <p class="text-[11px] text-slate-500">Sin eventos registrados.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Acciones disponibles</h2>
                @if ($workflowActions === [])
                    <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">No hay acciones pendientes para este estado.</p>
                @else
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($workflowActions as $action)
                            @if ($action === 'receive')
                                <flux:button type="button" size="sm" variant="primary" wire:click="receiveDocument">{{ $actionLabels[$action] }}</flux:button>
                            @else
                                <flux:button type="button" size="sm" variant="{{ in_array($action, ['reject', 'cancel'], true) ? 'danger' : 'outline' }}" wire:click="openActionModal('{{ $action }}')">{{ $actionLabels[$action] }}</flux:button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Adjuntos del movimiento</h2>
                <input wire:model="movementAttachments" type="file" multiple class="mt-2 block w-full text-[11px] file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 dark:file:bg-slate-800" />
                @error('movementAttachments.*') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-[10px] text-slate-500">Se adjuntan a la siguiente accion que ejecutes.</p>
            </div>
        </div>
    </div>

    <x-platform.modal compact :show="$actionModal !== null" max-width="max-w-lg">
        <div class="flex items-center justify-between gap-2">
            <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ $actionLabels[$actionModal] ?? 'Accion' }}</h2>
            <flux:button variant="ghost" size="sm" wire:click="closeActionModal" type="button">Cerrar</flux:button>
        </div>

        <div class="mt-3 space-y-2">
            @if ($actionModal === 'derive')
                <div>
                    <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Area destino</label>
                    <select wire:model="derive_destination_area_id" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">Seleccionar</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                        @endforeach
                    </select>
                    @error('derive_destination_area_id') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Nuevo responsable</label>
                    <select wire:model="derive_current_user_id" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">Seleccionar</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('derive_current_user_id') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <textarea wire:model="derive_notes" rows="2" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Nota de derivacion"></textarea>
            @elseif ($actionModal === 'observe')
                <textarea wire:model="observation" rows="3" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Detalle de la observacion"></textarea>
                @error('observation') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            @elseif ($actionModal === 'process' || $actionModal === 'attend')
                <textarea wire:model="process_notes" rows="3" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Nota de proceso o atencion"></textarea>
            @elseif ($actionModal === 'approve')
                <textarea wire:model="approval_comments" rows="3" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Comentario de aprobacion"></textarea>
            @elseif ($actionModal === 'reject')
                <textarea wire:model="rejection_comments" rows="3" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Motivo de rechazo"></textarea>
                @error('rejection_comments') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            @elseif ($actionModal === 'archive')
                <textarea wire:model="archive_notes" rows="3" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Nota de archivo"></textarea>
            @elseif ($actionModal === 'reopen')
                <textarea wire:model="reopen_notes" rows="3" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Motivo de reapertura"></textarea>
            @elseif ($actionModal === 'cancel')
                <textarea wire:model="annulment_reason" rows="3" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Motivo de anulacion"></textarea>
                @error('annulment_reason') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            @elseif ($actionModal === 'close')
                <textarea wire:model="close_notes" rows="3" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Nota de cierre"></textarea>
            @endif
        </div>

        @if ($actionModal !== null && $actionModal !== 'receive')
            <div class="mt-4 flex justify-end gap-2">
                <flux:button type="button" variant="outline" size="sm" wire:click="closeActionModal">Cancelar</flux:button>
                <flux:button
                    type="button"
                    variant="primary"
                    size="sm"
                    wire:click="{{ match ($actionModal) {
                        'derive' => 'deriveDocument',
                        'observe' => 'observeDocument',
                        'process' => 'sendToReview',
                        'attend' => 'attendDocument',
                        'approve' => 'approveDocument',
                        'reject' => 'rejectDocument',
                        'archive' => 'archiveDocument',
                        'reopen' => 'reopenDocument',
                        'cancel' => 'cancelDocument',
                        'close' => 'closeDocument',
                        default => 'closeActionModal',
                    } }}"
                >
                    Confirmar
                </flux:button>
            </div>
        @endif
    </x-platform.modal>
</div>
