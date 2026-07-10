<div class="space-y-4">
    <x-mechanics.page-header :title="$title" description="Registro de maquinaria con ubicacion por obra.">
        @can('mecanica.exportar')
            <flux:button variant="outline" size="sm" icon="document-text" wire:click="openEquipmentsReportPdf">PDF</flux:button>
            <flux:button variant="outline" size="sm" icon="table-cells" href="{{ route('mechanics.report.equipments.excel') }}">Excel</flux:button>
        @endcan
        @can('equipos.crear')
            <flux:button variant="outline" size="sm" href="{{ route('mechanics.equipment-types') }}" wire:navigate>Tipos</flux:button>
            <flux:button variant="primary" size="sm" icon="plus" wire:click="openCreateModal">Nuevo equipo</flux:button>
        @endcan
    </x-mechanics.page-header>

    <x-mechanics.filter-strip>
        <div class="min-w-[10rem] flex-1">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Codigo, nombre, placa u obra" />
        </div>
        <div class="w-44">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Obra</label>
            <select wire:model.live="projectFilter" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->code }} · {{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-40">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</label>
            <select wire:model.live="statusFilter" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($statusOptions as $statusOption)
                    <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </div>
    </x-mechanics.filter-strip>

    <x-platform.compact-table dense :headers="['Codigo', 'Equipo', 'Obra', 'Estado', 'Km / Hrs', '']">
        @forelse ($equipments as $equipment)
            <tr wire:key="fleet-eq-{{ $equipment->id }}">
                <td class="whitespace-nowrap font-semibold text-slate-950 dark:text-white">{{ $equipment->internal_code }}</td>
                <td class="max-w-[14rem]">
                    <p class="truncate font-medium leading-tight text-slate-950 dark:text-white">{{ $equipment->name }}</p>
                    <p class="mt-0.5 truncate text-[10px] text-slate-500 dark:text-slate-400">{{ $equipment->typeLabel() }} · {{ $equipment->plate ?: 'Sin placa' }}</p>
                </td>
                <td class="max-w-[12rem]">
                    @if ($equipment->workProject)
                        <p class="truncate font-medium leading-tight text-slate-950 dark:text-white">{{ $equipment->workProject->code }}</p>
                        <p class="mt-0.5 truncate text-[10px] text-slate-500 dark:text-slate-400">{{ $equipment->workProject->name }}</p>
                    @else
                        <span class="text-slate-400">Sin obra</span>
                    @endif
                </td>
                <td class="whitespace-nowrap">
                    <x-platform.status-badge :value="$equipment->operational_status" size="xs" />
                </td>
                <td class="whitespace-nowrap tabular-nums">{{ number_format((float) ($equipment->odometer_km ?? 0)) }} km · {{ number_format((float) ($equipment->hour_meter ?? 0), 1) }} h</td>
                <td class="!px-1.5 !py-1">
                    <x-mechanics.row-actions
                        :edit="auth()->user()->can('equipos.editar') ? 'openEditModal('.$equipment->id.')' : null"
                        :delete="auth()->user()->can('equipos.eliminar') ? 'deleteEquipment('.$equipment->id.')' : null"
                        delete-confirm="Eliminar equipo?"
                    />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="!py-5 text-center text-[11px] text-slate-500 dark:text-slate-400">No hay equipos registrados.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <x-mechanics.pagination>{{ $equipments->links() }}</x-mechanics.pagination>

    <x-platform.modal compact :show="$showFormModal" max-width="max-w-3xl">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ $editingEquipmentId ? 'Editar equipo' : 'Nuevo equipo' }}</h2>
            <flux:button variant="ghost" size="sm" wire:click="closeModals">Cerrar</flux:button>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-2">
            @if ($editingEquipmentId)
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Codigo interno</label>
                    <input value="{{ $internal_code }}" readonly class="mt-1 block h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300" />
                </div>
            @else
                <div class="md:col-span-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-2.5 py-2 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300">
                    El codigo interno se genera automaticamente al guardar (correlativo por empresa).
                </div>
            @endif
            <div @class(['md:col-span-2' => ! $editingEquipmentId])>
                <div class="flex items-end justify-between gap-2">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Tipo de equipo</label>
                    @can('equipos.crear')
                        <a href="{{ route('mechanics.equipment-types') }}" wire:navigate class="text-[10px] font-medium text-cyan-700 hover:underline dark:text-cyan-400">Gestionar tipos</a>
                    @endcan
                </div>
                <select wire:model="equipment_type_id" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('equipment_type_id') border-rose-500 @enderror">
                    <option value="">Seleccionar tipo</option>
                    @foreach ($equipmentTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('equipment_type_id') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Nombre</label>
                <input wire:model="name" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('name') border-rose-500 @enderror" />
                @error('name') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Obra actual</label>
                <select wire:model="work_project_id" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('work_project_id') border-rose-500 @enderror">
                    <option value="">Sin obra asignada</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->code }} · {{ $project->name }}</option>
                    @endforeach
                </select>
                @error('work_project_id') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-[10px] text-slate-500">Indica en que obra se encuentra operando el equipo.</p>
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Marca / modelo</label>
                <input wire:model="brand" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('brand') border-rose-500 @enderror" placeholder="Marca" />
                @error('brand') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                <input wire:model="model" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('model') border-rose-500 @enderror" placeholder="Modelo" />
                @error('model') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Serie / placa / año</label>
                <input wire:model="serial_number" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('serial_number') border-rose-500 @enderror" placeholder="Serie" />
                @error('serial_number') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                <input wire:model="plate" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('plate') border-rose-500 @enderror" placeholder="Placa" />
                @error('plate') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                <input wire:model="year" type="number" min="1900" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('year') border-rose-500 @enderror" placeholder="Año" />
                @error('year') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Ciudad</label>
                <input wire:model="city" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('city') border-rose-500 @enderror" />
                @error('city') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Color</label>
                <input wire:model="color" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('color') border-rose-500 @enderror" />
                @error('color') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Responsable</label>
                <select wire:model="responsible_user_id" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('responsible_user_id') border-rose-500 @enderror">
                    <option value="">Sin asignar</option>
                    @foreach ($responsibleUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
                @error('responsible_user_id') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Estado operativo</label>
                <select wire:model="operational_status" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('operational_status') border-rose-500 @enderror">
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
                @error('operational_status') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2">
                <div class="flex-1">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Km actual</label>
                    <input wire:model="odometer_km" type="number" min="0" step="0.01" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('odometer_km') border-rose-500 @enderror" />
                    @error('odometer_km') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex-1">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Horometro</label>
                    <input wire:model="hour_meter" type="number" min="0" step="0.1" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('hour_meter') border-rose-500 @enderror" />
                    @error('hour_meter') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Fecha adquisicion</label>
                <input type="date" wire:model="acquisition_date" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('acquisition_date') border-rose-500 @enderror" />
                @error('acquisition_date') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Observaciones</label>
                <textarea wire:model="observations" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('observations') border-rose-500 @enderror"></textarea>
                @error('observations') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Fotos</label>
                <input type="file" wire:model="equipment_photos" multiple accept="image/*" class="mt-1 block w-full text-xs @error('equipment_photos.*') text-rose-600 @enderror" />
                @error('equipment_photos.*') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500">Documentos</label>
                <input type="file" wire:model="equipment_documents" multiple accept=".pdf,image/*" class="mt-1 block w-full text-xs @error('equipment_documents.*') text-rose-600 @enderror" />
                @error('equipment_documents.*') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-4 flex justify-end gap-2">
            <flux:button variant="outline" size="sm" wire:click="closeModals">Cancelar</flux:button>
            <flux:button variant="primary" size="sm" wire:click="saveEquipment">Guardar</flux:button>
        </div>
    </x-platform.modal>

    <x-platform.modal compact :show="$showDetailModal" max-width="max-w-4xl">
        @if ($selectedEquipment)
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ $selectedEquipment->internal_code }}</h2>
                    <p class="text-xs text-slate-500">{{ $selectedEquipment->name }} · {{ $selectedEquipment->typeLabel() }}</p>
                </div>
                <flux:button variant="ghost" size="sm" wire:click="closeModals">Cerrar</flux:button>
            </div>
            <dl class="mt-4 grid gap-2 text-xs text-slate-700 dark:text-slate-300 md:grid-cols-2">
                <div><dt class="text-[10px] uppercase text-slate-500">Tipo</dt><dd class="font-medium">{{ $selectedEquipment->typeLabel() }}</dd></div>
                <div><dt class="text-[10px] uppercase text-slate-500">Estado</dt><dd><x-platform.status-badge :value="$selectedEquipment->operational_status" size="xs" /></dd></div>
                <div><dt class="text-[10px] uppercase text-slate-500">Obra actual</dt><dd class="font-medium">{{ $selectedEquipment->workProject ? $selectedEquipment->workProject->code.' · '.$selectedEquipment->workProject->name : 'Sin obra asignada' }}</dd></div>
                <div><dt class="text-[10px] uppercase text-slate-500">Responsable</dt><dd class="font-medium">{{ $selectedEquipment->responsibleUser?->name ?? '—' }}</dd></div>
            </dl>

            <div class="mt-5">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Historial de revisiones tecnicas</h3>
                    @can('revisiones.crear')
                        <flux:button variant="outline" size="xs" href="{{ route('mechanics.inspections') }}" wire:navigate>Nueva revision</flux:button>
                    @endcan
                </div>
                <x-platform.compact-table dense :headers="['Revision', 'Vencimiento', 'Resultado', 'Centro', 'Estado']">
                    @forelse ($selectedEquipment->technicalInspections as $inspection)
                        <tr wire:key="eq-insp-{{ $inspection->id }}">
                            <td class="whitespace-nowrap tabular-nums">{{ $inspection->reviewed_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="whitespace-nowrap tabular-nums">{{ $inspection->due_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="max-w-[10rem] truncate">{{ $inspection->result }}</td>
                            <td class="max-w-[10rem] truncate">{{ $inspection->inspection_center ?? '—' }}</td>
                            <td class="whitespace-nowrap"><x-platform.status-badge :value="$inspection->status" size="xs" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="!py-5 text-center text-[11px] text-slate-500">Sin revisiones registradas para este equipo.</td>
                        </tr>
                    @endforelse
                </x-platform.compact-table>
            </div>

            <div class="mt-4">
                <p class="text-[10px] font-semibold uppercase text-slate-500">Adjuntos</p>
                <ul class="mt-1 space-y-0.5 text-xs text-cyan-700 dark:text-cyan-400">
                    @foreach ($selectedEquipment->getMedia('equipment_photos') as $media)
                        <li><a href="{{ $media->getUrl() }}" target="_blank" class="underline">Foto: {{ $media->name }}</a></li>
                    @endforeach
                    @foreach ($selectedEquipment->getMedia('equipment_documents') as $media)
                        <li>
                            <button type="button" wire:click="openEquipmentDocument(@js($media->getUrl()), @js($media->name), @js($media->mime_type))" class="underline">
                                Doc: {{ $media->name }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </x-platform.modal>

    <x-platform.pdf-viewer-modal
        :show="$showPdfModal"
        :url="$pdfViewerUrl"
        :title="$pdfViewerTitle"
        :subtitle="$pdfViewerSubtitle"
        :allowExternalOpen="false"
    />
</div>
