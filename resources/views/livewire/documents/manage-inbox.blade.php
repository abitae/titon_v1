<div class="space-y-4">
    <div class="grid gap-2 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">En entrada</p>
            <p class="mt-0.5 text-xl font-semibold tabular-nums text-slate-950 dark:text-white">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pendientes</p>
            <p class="mt-0.5 text-xl font-semibold tabular-nums text-slate-950 dark:text-white">{{ number_format($summary['pending']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Vencidos</p>
            <p class="mt-0.5 text-xl font-semibold tabular-nums text-rose-600 dark:text-rose-400">{{ number_format($summary['expired']) }}</p>
        </div>
    </div>

    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-950 dark:text-white">Bandeja de entrada</h1>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Documentos asignados a ti con seguimiento y trazabilidad.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button variant="outline" href="{{ route('documents.outbox') }}" wire:navigate size="sm">Salida</flux:button>
            <flux:button variant="outline" href="{{ route('documents.projects') }}" wire:navigate size="sm">Por obra</flux:button>
            @can('documents.crear')
                <flux:button type="button" variant="primary" wire:click="openCreateModal" size="sm">Registrar documento</flux:button>
            @endcan
        </div>
    </div>

    <x-platform.filter-bar>
        <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Codigo, asunto o numero" />
        </div>
        <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</label>
            <select wire:model.live="statusFilter" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($statusOptions as $statusOption)
                    <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Prioridad</label>
            <select wire:model.live="priorityFilter" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas</option>
                @foreach ($priorityOptions as $priorityOption)
                    <option value="{{ $priorityOption->value() }}">{{ $priorityOption->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Obra</label>
            <select wire:model.live="projectFilter" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
    </x-platform.filter-bar>

    <x-platform.compact-table dense :headers="['Expediente', 'Asunto', 'Ubicacion', 'Plazo', 'Estado', 'Prioridad', '']">
        @forelse ($documents as $document)
            <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="inbox-document-{{ $document->id }}">
                <td class="px-2 py-1">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $document->code }}</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $document->document_number ?: 'Sin numero' }}</p>
                </td>
                <td class="px-2 py-1">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $document->subject }}</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $document->documentType?->name ?? 'Sin tipo' }}</p>
                </td>
                <td class="px-2 py-1">
                    <p>{{ $document->currentLocationLabel() }}</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $document->currentUser?->name ?? 'Sin responsable' }}</p>
                </td>
                <td class="px-2 py-1 whitespace-nowrap">
                    <p>{{ $document->due_date?->format('d/m/Y') ?? 'Sin plazo' }}</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $document->pendingHours() }} h</p>
                </td>
                <td class="px-2 py-1"><x-platform.status-badge :value="$document->status" size="xs" /></td>
                <td class="px-2 py-1"><x-platform.status-badge :value="$document->priority" size="xs" /></td>
                <td class="!px-1.5 !py-1">
                    <x-platform.action-buttons :edit-href="route('documents.show', $document)" :edit-navigate="false" />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-xs text-slate-500 dark:text-slate-400">No hay documentos en tu bandeja de entrada.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $documents->links() }}
    </div>

    <x-platform.modal compact :show="$showCreateModal" max-width="max-w-3xl">
        <div class="flex items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-semibold text-slate-950 dark:text-white">Registrar documento</h2>
                <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">Codigo {{ $code }} · se confirma al guardar.</p>
            </div>
            <flux:button variant="ghost" size="sm" wire:click="closeModal" type="button">Cerrar</flux:button>
        </div>

        <div class="mt-3 grid gap-2 md:grid-cols-2">
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Numero de documento</label>
                <input wire:model="document_number" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('document_number') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Tipo</label>
                <select wire:model="document_type_id" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($documentTypes as $documentType)
                        <option value="{{ $documentType->id }}">{{ $documentType->name }}</option>
                    @endforeach
                </select>
                @error('document_type_id') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Asunto</label>
                <input wire:model="subject" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('subject') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Descripcion</label>
                <textarea wire:model="description" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Obra</label>
                <select wire:model="work_project_id" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Sin obra</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Prioridad</label>
                <select wire:model="priority" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @foreach ($priorityOptions as $priorityOption)
                        <option value="{{ $priorityOption->value() }}">{{ $priorityOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Area origen</label>
                <select wire:model="origin_area_id" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Area destino</label>
                <select wire:model="destination_area_id" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Responsable</label>
                <select wire:model="current_user_id" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Emision</label>
                <input wire:model="issue_date" type="date" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Recepcion</label>
                <input wire:model="reception_date" type="date" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Vencimiento</label>
                <input wire:model="due_date" type="date" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div class="md:col-span-2">
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Observaciones</label>
                <textarea wire:model="observations" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Adjuntos</label>
                <input wire:model="attachments" type="file" multiple class="mt-1 block w-full text-xs text-slate-600 file:me-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-[11px] dark:text-slate-300 dark:file:bg-slate-800" />
                @error('attachments.*') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-4 flex justify-end gap-2">
            <flux:button type="button" variant="outline" size="sm" wire:click="closeModal">Cancelar</flux:button>
            <flux:button type="button" variant="primary" size="sm" wire:click="saveDocument">Guardar</flux:button>
        </div>
    </x-platform.modal>
</div>
