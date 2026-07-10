<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Bandeja de salida</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Sigue los documentos que registraste y monitorea su estado actual, responsable y destino.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('modules.documents') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Entrada</a>
            <a href="{{ route('documents.projects') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Por obra</a>
        </div>
    </div>

    <x-platform.filter-bar>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Codigo o asunto" />
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
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Prioridad</label>
            <select wire:model.live="priorityFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas</option>
                @foreach ($priorityOptions as $priorityOption)
                    <option value="{{ $priorityOption->value() }}">{{ $priorityOption->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Obra</label>
            <select wire:model.live="projectFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
    </x-platform.filter-bar>

    <x-platform.compact-table :headers="['Codigo', 'Asunto', 'Destino', 'Responsable', 'Estado', 'Acciones']">
        @forelse ($documents as $document)
            <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="outbox-document-{{ $document->id }}">
                <td class="px-2.5 py-1.5 font-medium text-slate-950 dark:text-white">{{ $document->code }}</td>
                <td class="px-2.5 py-1.5">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $document->subject }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ $document->project?->name ?? 'Sin obra' }}</p>
                </td>
                <td class="px-2.5 py-1.5">{{ $document->destinationArea?->name ?? 'Sin area' }}</td>
                <td class="px-2.5 py-1.5">{{ $document->currentUser?->name ?? 'Sin responsable' }}</td>
                <td class="px-2.5 py-1.5"><x-platform.status-badge :value="$document->status" /></td>
                <td class="px-2.5 py-1.5">
                    <x-platform.action-buttons :edit-href="route('documents.show', $document)" />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No hay documentos registrados por este usuario.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-3xl border border-slate-200 bg-white px-2.5 py-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $documents->links() }}
    </div>
</div>
