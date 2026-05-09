<div class="space-y-6">
    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Obras registradas</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">En ejecución</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($summary['in_progress']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Presupuesto estimado</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">S/ {{ number_format($summary['estimated_budget'], 2) }}</p>
        </div>
    </section>

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Obras</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Controla las obras activas, responsables, filtros y archivos asociados.</p>
        </div>

        <button type="button" wire:click="openCreateModal" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400">
            Nueva obra
        </button>
    </div>

    <x-platform.filter-bar>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Código, obra o cliente" />
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Ciudad</label>
            <select wire:model.live="cityFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas</option>
                @foreach ($cities as $cityOption)
                    <option value="{{ $cityOption->name }}">{{ $cityOption->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Responsable</label>
            <select wire:model.live="responsibleFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($responsibleUsers as $responsibleUser)
                    <option value="{{ $responsibleUser->id }}">{{ $responsibleUser->name }}</option>
                @endforeach
            </select>
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
    </x-platform.filter-bar>

    <x-platform.compact-table :headers="['Código', 'Obra', 'Responsable', 'Estado', 'Presupuesto', 'Acciones']">
        @forelse ($projects as $project)
            <tr class="text-sm text-slate-700 dark:text-slate-200" wire:key="project-row-{{ $project->id }}">
                <td class="px-6 py-4 font-medium text-slate-950 dark:text-white">{{ $project->code }}</td>
                <td class="px-6 py-4">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $project->name }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ $project->city ?: 'Sin ciudad' }} · {{ $project->client_name ?: 'Sin cliente' }}</p>
                </td>
                <td class="px-6 py-4">{{ $project->responsibleUser?->name ?? 'Sin responsable' }}</td>
                <td class="px-6 py-4"><x-platform.status-badge :value="$project->status" /></td>
                <td class="px-6 py-4">S/ {{ number_format((float) $project->estimated_budget, 2) }}</td>
                <td class="px-6 py-4">
                    <x-platform.action-buttons
                        :view="'openDetailModal('.$project->id.')'"
                        :edit="'openEditModal('.$project->id.')'"
                        :delete="'deleteProject('.$project->id.')'"
                        delete-confirm="¿Eliminar esta obra?"
                    />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No hay obras registradas para la empresa activa.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-3xl border border-slate-200 bg-white px-6 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $projects->links() }}
    </div>

    <x-platform.modal :show="$showFormModal">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $editingProjectId ? 'Editar obra' : 'Nueva obra' }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Registra datos generales, responsable, filtros y archivos asociados.</p>
            </div>
            <button type="button" wire:click="closeModals" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Código</label>
                <input wire:model="code" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('code') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nombre</label>
                <input wire:model="name" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Ciudad</label>
                <input wire:model="city" list="project-cities" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                <datalist id="project-cities">
                    @foreach ($cities as $cityOption)
                        <option value="{{ $cityOption->name }}"></option>
                    @endforeach
                </datalist>
                @error('city') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Cliente</label>
                <input wire:model="client_name" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('client_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Dirección</label>
                <input wire:model="address" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('address') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Responsable</label>
                <select wire:model="responsible_user_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($responsibleUsers as $responsibleUser)
                        <option value="{{ $responsibleUser->id }}">{{ $responsibleUser->name }}</option>
                    @endforeach
                </select>
                @error('responsible_user_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Estado</label>
                <select wire:model="status" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
                @error('status') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Inicio</label>
                <input wire:model="start_date" type="date" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('start_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Fin estimado</label>
                <input wire:model="estimated_end_date" type="date" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('estimated_end_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Presupuesto estimado</label>
                <input wire:model="estimated_budget" type="number" step="0.01" min="0" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('estimated_budget') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Descripción</label>
                <textarea wire:model="description" rows="4" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                @error('description') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Archivos</label>
                <input wire:model="attachments" type="file" multiple class="mt-2 block w-full rounded-xl border border-dashed border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('attachments.*') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" wire:click="closeModals" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancelar</button>
            <button type="button" wire:click="saveProject" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400">Guardar</button>
        </div>
    </x-platform.modal>

    <x-platform.modal :show="$showDetailModal" max-width="max-w-3xl">
        @if ($selectedProject)
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $selectedProject->name }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $selectedProject->code }} · {{ $selectedProject->city ?: 'Sin ciudad' }}</p>
                </div>
                <button type="button" wire:click="closeModals" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Estado</p>
                    <div class="mt-2"><x-platform.status-badge :value="$selectedProject->status" /></div>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Presupuesto</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">S/ {{ number_format((float) $selectedProject->estimated_budget, 2) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Responsable</p>
                    <p class="mt-2 text-sm text-slate-900 dark:text-white">{{ $selectedProject->responsibleUser?->name ?? 'Sin responsable' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Fechas</p>
                    <p class="mt-2 text-sm text-slate-900 dark:text-white">{{ $selectedProject->start_date?->format('d/m/Y') ?: 'Sin fecha' }} → {{ $selectedProject->estimated_end_date?->format('d/m/Y') ?: 'Sin fecha' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800 md:col-span-2">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Descripción</p>
                    <p class="mt-2 text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $selectedProject->description ?: 'Sin descripción registrada.' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800 md:col-span-2">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Archivos adjuntos</p>
                    <div class="mt-3 flex flex-col gap-2">
                        @forelse ($selectedProject->attachments as $attachment)
                            <a href="{{ $attachment->url() }}" target="_blank" class="text-sm font-medium text-cyan-700 hover:text-cyan-600 dark:text-cyan-300 dark:hover:text-cyan-200">
                                {{ $attachment->original_name }}
                            </a>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">No hay archivos asociados.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </x-platform.modal>
</div>
