<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ $document->subject }}</h1>
                <x-platform.status-badge :value="$document->status" />
                <x-platform.status-badge :value="$document->priority" />
            </div>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                {{ $document->code }} · {{ $document->document_number ?: 'Sin numero' }} · {{ $document->documentType?->name ?? 'Sin tipo' }} · {{ $document->project?->name ?? 'Sin obra' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('modules.documents') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Volver a entrada</a>
            <a href="{{ route('documents.timeline', $document) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Timeline completo</a>
        </div>
    </div>

    <section class="grid gap-4 lg:grid-cols-[1.2fr,0.8fr]">
        <div class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Resumen</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Origen</p>
                        <p class="mt-2 text-sm text-slate-900 dark:text-white">{{ $document->originArea?->name ?? 'Sin area' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Destino actual</p>
                        <p class="mt-2 text-sm text-slate-900 dark:text-white">{{ $document->currentLocationLabel() }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Responsable actual</p>
                        <p class="mt-2 text-sm text-slate-900 dark:text-white">{{ $document->currentUser?->name ?? 'Sin responsable' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Registro</p>
                        <p class="mt-2 text-sm text-slate-900 dark:text-white">{{ $document->createdByUser?->name ?? 'Sin usuario' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Emision</p>
                        <p class="mt-2 text-sm text-slate-900 dark:text-white">{{ $document->issue_date?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Recepcion</p>
                        <p class="mt-2 text-sm text-slate-900 dark:text-white">{{ $document->reception_date?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Vencimiento</p>
                        <p class="mt-2 text-sm text-slate-900 dark:text-white">{{ $document->due_date?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Tiempo pendiente</p>
                        <p class="mt-2 text-sm text-slate-900 dark:text-white">{{ $document->pendingHours() }} horas</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Descripcion</p>
                        <p class="mt-2 text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $document->description ?: 'Sin descripcion registrada.' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Observaciones</p>
                        <p class="mt-2 text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $document->observations ?: 'Sin observaciones registradas.' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Archivos del documento</h2>
                    <span class="text-sm text-slate-500 dark:text-slate-400">{{ $document->getMedia('attachments')->count() }} adjuntos</span>
                </div>
                <div class="mt-4 flex flex-col gap-3">
                    @forelse ($document->getMedia('attachments') as $media)
                        <a href="{{ $media->getUrl() }}" target="_blank" class="rounded-2xl border border-slate-200 px-2.5 py-1.5 text-sm font-medium text-cyan-700 hover:border-cyan-200 hover:bg-cyan-50 dark:border-slate-800 dark:text-cyan-300">
                            {{ $media->file_name }}
                        </a>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">No hay archivos adjuntos.</p>
                    @endforelse
                </div>
                <div class="mt-4">
                    <input wire:model="newAttachments" type="file" multiple class="block w-full rounded-xl border border-dashed border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('newAttachments.*') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    <button type="button" wire:click="uploadAttachments" class="mt-3 rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Subir adjuntos</button>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Timeline resumido</h2>
                <div class="mt-4 space-y-4">
                    @forelse ($timeline->take(5) as $entry)
                        <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-slate-950 dark:text-white">{{ $entry['title'] }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $entry['actor'] ?? 'Sistema' }} · {{ optional($entry['created_at'])->format('d/m/Y H:i') }}</p>
                                </div>
                                <x-platform.status-badge :value="$entry['status']" />
                            </div>
                            <p class="mt-3 text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $entry['description'] }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">Aun no hay eventos registrados.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Acciones del flujo</h2>
                <div class="mt-4 grid gap-3">
                    <button type="button" wire:click="receiveDocument" class="rounded-xl border border-slate-300 px-4 py-2 text-left text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Recibir documento</button>
                    <button type="button" wire:click="sendToReview" class="rounded-xl border border-slate-300 px-4 py-2 text-left text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Marcar en proceso</button>
                    <button type="button" wire:click="attendDocument" class="rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2 text-left text-sm font-medium text-cyan-700 dark:border-cyan-900/40 dark:bg-cyan-950/30 dark:text-cyan-300">Atender documento</button>
                    <button type="button" wire:click="approveDocument" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-left text-sm font-medium text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300">Aprobar documento</button>
                    <button type="button" wire:click="archiveDocument" class="rounded-xl border border-violet-200 bg-violet-50 px-4 py-2 text-left text-sm font-medium text-violet-700 dark:border-violet-900/40 dark:bg-violet-950/30 dark:text-violet-300">Archivar documento</button>
                    <button type="button" wire:click="reopenDocument" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-left text-sm font-medium text-amber-700 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-300">Reabrir documento</button>
                    <button type="button" wire:click="cancelDocument" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-left text-sm font-medium text-rose-700 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300">Anular documento</button>
                    <button type="button" wire:click="closeDocument" class="rounded-xl border border-slate-300 px-4 py-2 text-left text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Cerrar documento</button>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Adjuntos del movimiento</h2>
                <input wire:model="movementAttachments" type="file" multiple class="mt-4 block w-full rounded-xl border border-dashed border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('movementAttachments.*') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Se adjuntaran a la siguiente accion documentaria que ejecutes.</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Derivar</h2>
                <div class="mt-4 grid gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Area destino</label>
                        <select wire:model="derive_destination_area_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="">Seleccionar</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                        @error('derive_destination_area_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nuevo responsable</label>
                        <select wire:model="derive_current_user_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="">Seleccionar</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('derive_current_user_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nota</label>
                        <textarea wire:model="derive_notes" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                        @error('derive_notes') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="button" wire:click="deriveDocument" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Derivar</button>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Observacion</h2>
                <textarea wire:model="observation" rows="4" class="mt-4 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Detalle de la observacion"></textarea>
                @error('observation') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                <button type="button" wire:click="observeDocument" class="mt-3 rounded-xl border border-orange-200 bg-orange-50 px-4 py-2 text-sm font-medium text-orange-700 dark:border-orange-900/40 dark:bg-orange-950/30 dark:text-orange-300">Observar</button>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Proceso, archivo y reapertura</h2>
                <div class="mt-4 grid gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nota de proceso / atencion</label>
                        <textarea wire:model="process_notes" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nota de archivo</label>
                        <textarea wire:model="archive_notes" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Motivo de reapertura</label>
                        <textarea wire:model="reopen_notes" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Motivo de anulacion</label>
                        <textarea wire:model="annulment_reason" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                        @error('annulment_reason') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Aprobacion o rechazo</h2>
                <div class="mt-4 grid gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Comentario de aprobacion</label>
                        <textarea wire:model="approval_comments" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Motivo de rechazo</label>
                        <textarea wire:model="rejection_comments" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                        @error('rejection_comments') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nota de cierre</label>
                        <textarea wire:model="close_notes" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                    </div>
                    <button type="button" wire:click="rejectDocument" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300">Rechazar documento</button>
                </div>
            </div>
        </div>
    </section>
</div>
