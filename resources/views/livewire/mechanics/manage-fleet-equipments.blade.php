<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700 dark:text-cyan-400">Mecanica</p>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ $title }}</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Registro corporativo con alcance multiempresa.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a wire:navigate href="{{ route('modules.mechanics') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm dark:border-slate-600">Dashboard</a>
            @can('mecanica.exportar')
                <a href="{{ route('mechanics.report.equipments.pdf') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm dark:border-slate-600">PDF</a>
                <a href="{{ route('mechanics.report.equipments.excel') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm dark:border-slate-600">Excel</a>
            @endcan
            @can('equipos.crear')
                <button type="button" wire:click="openCreateModal" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white dark:bg-cyan-500 dark:text-slate-950">
                    Nuevo equipo
                </button>
            @endcan
        </div>
    </div>

    <x-platform.filter-bar>
        <div class="md:col-span-2">
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Buscar</label>
            <input
                wire:model.live.debounce.300ms="search"
                class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                placeholder="Codigo, nombre o placa"
            />
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Estado operativo</label>
            <select wire:model.live="statusFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($statusOptions as $statusOption)
                    <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </div>
    </x-platform.filter-bar>

    <x-platform.compact-table :headers="['Codigo', 'Equipo', 'Obra', 'Estado', 'Km / Hrs', 'Acciones']">
        @forelse ($equipments as $equipment)
            <tr class="text-sm text-slate-700 dark:text-slate-200" wire:key="fleet-eq-{{ $equipment->id }}">
                <td class="px-6 py-4 font-semibold text-slate-950 dark:text-white">{{ $equipment->internal_code }}</td>
                <td class="px-6 py-4">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $equipment->name }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ $equipment->equipment_type }} · {{ $equipment->plate ?: 'Sin placa' }}</p>
                </td>
                <td class="px-6 py-4">{{ $equipment->workProject?->code ?? 'Sin obra' }}</td>
                <td class="px-6 py-4">{{ str_replace('_', ' ', $equipment->operational_status) }}</td>
                <td class="px-6 py-4">{{ number_format((float) ($equipment->odometer_km ?? 0)) }} km · {{ number_format((float) ($equipment->hour_meter ?? 0), 1) }} h</td>
                <td class="px-6 py-4">
                    <x-platform.action-buttons
                        :view="'openDetailModal('.$equipment->id.')'"
                        :edit="auth()->user()->can('equipos.editar') ? 'openEditModal('.$equipment->id.')' : null"
                        :delete="auth()->user()->can('equipos.eliminar') ? 'deleteEquipment('.$equipment->id.')' : null"
                        delete-confirm="Eliminar equipo?"
                    />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No hay equipos registrados.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-3xl border border-slate-200 bg-white px-6 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $equipments->links() }}
    </div>

    <x-platform.modal :show="$showFormModal">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $editingEquipmentId ? 'Editar equipo' : 'Nuevo equipo' }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Completa datos operativos y adjunta fotos o documentos.</p>
            </div>
            <button type="button" wire:click="closeModals" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Codigo interno</label>
                <input wire:model="internal_code" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('internal_code') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Tipo</label>
                <input wire:model="equipment_type" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('equipment_type') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nombre</label>
                <input wire:model="name" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Marca / modelo</label>
                <input wire:model="brand" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Marca" />
                <input wire:model="model" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Modelo" />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Serie / placa / año</label>
                <input wire:model="serial_number" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Serie" />
                <input wire:model="plate" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Placa" />
                <input wire:model="year" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Año" />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Ciudad</label>
                <input wire:model="city" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Color</label>
                <input wire:model="color" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Obra</label>
                <select wire:model="work_project_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Sin obra</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->code }} · {{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Responsable</label>
                <select wire:model="responsible_user_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Sin asignar</option>
                    @foreach ($responsibleUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Estado operativo</label>
                <select wire:model="operational_status" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Km actual</label>
                    <input wire:model="odometer_km" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Horometro</label>
                    <input wire:model="hour_meter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Fecha adquisicion</label>
                <input type="date" wire:model="acquisition_date" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Observaciones</label>
                <textarea wire:model="observations" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Fotos del equipo</label>
                <input type="file" wire:model="equipment_photos" multiple class="mt-2 block w-full text-sm" />
                @error('equipment_photos.*') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Documentos</label>
                <input type="file" wire:model="equipment_documents" multiple class="mt-2 block w-full text-sm" />
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3">
            <button type="button" wire:click="closeModals" class="rounded-xl border border-slate-300 px-4 py-2 text-sm dark:border-slate-600">Cancelar</button>
            <button type="button" wire:click="saveEquipment" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white dark:bg-cyan-500 dark:text-slate-950">Guardar</button>
        </div>
    </x-platform.modal>

    <x-platform.modal :show="$showDetailModal">
        @if ($selectedEquipment)
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $selectedEquipment->internal_code }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $selectedEquipment->name }}</p>
                </div>
                <button type="button" wire:click="closeModals" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
            </div>
            <dl class="mt-6 grid gap-3 text-sm text-slate-700 dark:text-slate-300 md:grid-cols-2">
                <div><dt class="text-xs uppercase text-slate-500">Tipo</dt><dd class="font-medium">{{ $selectedEquipment->equipment_type }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-500">Estado</dt><dd class="font-medium">{{ $selectedEquipment->operational_status }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-500">Obra</dt><dd class="font-medium">{{ $selectedEquipment->workProject?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-500">Responsable</dt><dd class="font-medium">{{ $selectedEquipment->responsibleUser?->name ?? '—' }}</dd></div>
            </dl>
            <div class="mt-6">
                <p class="text-xs font-semibold uppercase text-slate-500">Adjuntos Spatie Media</p>
                <ul class="mt-2 space-y-1 text-sm text-cyan-700 dark:text-cyan-400">
                    @foreach ($selectedEquipment->getMedia('equipment_photos') as $media)
                        <li><a href="{{ $media->getUrl() }}" target="_blank" class="underline">Foto: {{ $media->name }}</a></li>
                    @endforeach
                    @foreach ($selectedEquipment->getMedia('equipment_documents') as $media)
                        <li><a href="{{ $media->getUrl() }}" target="_blank" class="underline">Doc: {{ $media->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </x-platform.modal>
</div>
