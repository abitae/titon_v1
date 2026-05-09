<div class="space-y-6">
    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Documentos en entrada</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Pendientes de atencion</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($summary['pending']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Vencidos</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($summary['expired']) }}</p>
        </div>
    </section>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Bandeja de entrada</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Registra documentos, revisa vencimientos y entra al detalle operativo con timeline y trazabilidad.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('documents.outbox') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Bandeja de salida</a>
            <a href="{{ route('documents.projects') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Documentos por obra</a>
            <button type="button" wire:click="openCreateModal" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Registrar documento</button>
        </div>
    </div>

    <x-platform.filter-bar>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Codigo o asunto" />
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
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Prioridad</label>
            <select wire:model.live="priorityFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas</option>
                @foreach ($priorityOptions as $priorityOption)
                    <option value="{{ $priorityOption->value() }}">{{ $priorityOption->label() }}</option>
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

    <x-platform.compact-table :headers="['Expediente', 'Asunto', 'Ubicacion', 'Plazo', 'Estado', 'Prioridad', 'Acciones']">
        @forelse ($documents as $document)
            <tr class="text-sm text-slate-700 dark:text-slate-200" wire:key="inbox-document-{{ $document->id }}">
                <td class="px-6 py-4">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $document->code }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ $document->document_number ?: 'Sin numero' }}</p>
                </td>
                <td class="px-6 py-4">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $document->subject }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ $document->documentType?->name ?? 'Sin tipo' }}</p>
                </td>
                <td class="px-6 py-4">
                    <p>{{ $document->currentLocationLabel() }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ $document->currentUser?->name ?? 'Sin responsable' }}</p>
                </td>
                <td class="px-6 py-4">
                    <p>{{ $document->due_date?->format('d/m/Y') ?? 'Sin plazo' }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ $document->pendingHours() }} h pendientes</p>
                </td>
                <td class="px-6 py-4"><x-platform.status-badge :value="$document->status" /></td>
                <td class="px-6 py-4"><x-platform.status-badge :value="$document->priority" /></td>
                <td class="px-6 py-4">
                    <a href="{{ route('documents.show', $document) }}" class="rounded-lg px-2 py-1 text-sm font-medium text-cyan-700 hover:bg-cyan-50 hover:text-cyan-600 dark:text-cyan-300">Abrir</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No hay documentos en la bandeja de entrada.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-3xl border border-slate-200 bg-white px-6 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $documents->links() }}
    </div>

    <x-platform.modal :show="$showCreateModal" max-width="max-w-4xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">Registrar documento</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Captura datos principales, responsable actual y adjuntos usando Media Library.</p>
            </div>
            <button type="button" wire:click="closeModal" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Codigo (automatico)</label>
                <input wire:model="code" readonly class="mt-2 block w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300" />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Se confirma al guardar usando la configuracion de correlativos de la empresa.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Numero de documento</label>
                <input wire:model="document_number" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('document_number') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Tipo de documento</label>
                <select wire:model="document_type_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($documentTypes as $documentType)
                        <option value="{{ $documentType->id }}">{{ $documentType->name }}</option>
                    @endforeach
                </select>
                @error('document_type_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Asunto</label>
                <input wire:model="subject" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('subject') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Descripcion</label>
                <textarea wire:model="description" rows="4" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                @error('description') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Obra</label>
                <select wire:model="work_project_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Sin obra</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
                @error('work_project_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Prioridad</label>
                <select wire:model="priority" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @foreach ($priorityOptions as $priorityOption)
                        <option value="{{ $priorityOption->value() }}">{{ $priorityOption->label() }}</option>
                    @endforeach
                </select>
                @error('priority') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Area origen</label>
                <select wire:model="origin_area_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
                @error('origin_area_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Area destino</label>
                <select wire:model="destination_area_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
                @error('destination_area_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Responsable actual</label>
                <select wire:model="current_user_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('current_user_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Emision</label>
                <input wire:model="issue_date" type="date" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('issue_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Recepcion</label>
                <input wire:model="reception_date" type="date" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('reception_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Vencimiento</label>
                <input wire:model="due_date" type="date" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('due_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Observaciones</label>
                <textarea wire:model="observations" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                @error('observations') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Adjuntos</label>
                <input wire:model="attachments" type="file" multiple class="mt-2 block w-full rounded-xl border border-dashed border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('attachments.*') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" wire:click="closeModal" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancelar</button>
            <button type="button" wire:click="saveDocument" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Guardar</button>
        </div>
    </x-platform.modal>
</div>
