<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Portafolio web</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Administra los proyectos publicados en el sitio público.</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">
            Nuevo proyecto
        </button>
    </div>

    <x-platform.filter-bar class="xl:grid-cols-2">
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Título, ciudad o cliente" />
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Estado</label>
            <select wire:model.live="publishedFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="all">Todos</option>
                <option value="published">Publicados</option>
                <option value="draft">Borradores</option>
            </select>
        </div>
    </x-platform.filter-bar>

    <x-platform.compact-table :headers="['Proyecto', 'Ciudad', 'Estado', 'Destacado', 'Orden', 'Acciones']">
        @forelse ($projects as $project)
            <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="showcase-project-{{ $project->id }}">
                <td class="px-2.5 py-1.5">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $project->title }}</p>
                    <p class="text-slate-500">{{ $project->client_name ?: 'Sin cliente' }}</p>
                </td>
                <td class="px-2.5 py-1.5">{{ $project->city ?: '—' }}</td>
                <td class="px-2.5 py-1.5">
                    <x-platform.status-badge :value="$project->is_published ? 'active' : 'inactive'" />
                </td>
                <td class="px-2.5 py-1.5">{{ $project->is_featured ? 'Sí' : 'No' }}</td>
                <td class="px-2.5 py-1.5">{{ $project->sort_order }}</td>
                <td class="px-2.5 py-1.5">
                    <x-platform.action-buttons
                        :edit="'openEditModal('.$project->id.')'"
                        :delete="'deleteProject('.$project->id.')'"
                        delete-confirm="¿Eliminar este proyecto del portafolio?"
                    />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">No hay proyectos en el portafolio.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div>{{ $projects->links() }}</div>

    <x-platform.modal :show="$showFormModal" max-width="max-w-2xl">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $editingProjectId ? 'Editar proyecto' : 'Nuevo proyecto' }}</h2>
            <button type="button" wire:click="closeModal" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100">Cerrar</button>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Título</label>
                <input wire:model.live="projectTitle" class="mt-2 block w-full rounded-xl border border-slate-300 px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('projectTitle') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Slug</label>
                <input wire:model="slug" class="mt-2 block w-full rounded-xl border border-slate-300 px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('slug') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Resumen</label>
                <textarea wire:model="summary" rows="2" class="mt-2 block w-full rounded-xl border border-slate-300 px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Descripción</label>
                <textarea wire:model="description" rows="4" class="mt-2 block w-full rounded-xl border border-slate-300 px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Ciudad</label>
                <input wire:model="city" class="mt-2 block w-full rounded-xl border border-slate-300 px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Cliente</label>
                <input wire:model="client_name" class="mt-2 block w-full rounded-xl border border-slate-300 px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Orden</label>
                <input wire:model="sort_order" type="number" min="0" class="mt-2 block w-full rounded-xl border border-slate-300 px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div class="space-y-3 pt-2">
                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <input wire:model="is_published" type="checkbox" class="rounded border-slate-300 text-cyan-600" />
                    Publicado
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <input wire:model="is_featured" type="checkbox" class="rounded border-slate-300 text-cyan-600" />
                    Destacado
                </label>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Imagen</label>
                @if ($currentImageUrl)
                    <img src="{{ $currentImageUrl }}" alt="" class="mt-2 h-24 rounded-lg object-cover" />
                @endif
                <input type="file" wire:model="image" accept="image/*" class="mt-2 block w-full text-sm" />
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" wire:click="closeModal" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">Cancelar</button>
            <button type="button" wire:click="saveProject" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white dark:bg-cyan-500 dark:text-slate-950">Guardar</button>
        </div>
    </x-platform.modal>
</div>
