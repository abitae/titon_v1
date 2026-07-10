<div class="space-y-4">
    <x-mechanics.page-header :title="$title">
        @can('revisiones.crear')
            <flux:button variant="primary" size="sm" icon="plus" wire:click="openCreate">Nueva revision</flux:button>
        @endcan
    </x-mechanics.page-header>

    <x-mechanics.filter-strip>
        <div class="min-w-[10rem] flex-1">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Equipo, centro o resultado" />
        </div>
        <div class="w-36">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</label>
            <select wire:model.live="statusFilter" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($statusOptions as $status)
                    <option value="{{ $status->value() }}">{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    </x-mechanics.filter-strip>

    <x-platform.compact-table dense :headers="['Equipo', 'Revision / venc.', 'Resultado', 'Estado', '']">
        @forelse ($rows as $row)
            <tr wire:key="insp-{{ $row->id }}">
                <td class="max-w-[12rem]">
                    <p class="truncate font-medium leading-tight text-slate-950 dark:text-white">{{ $row->equipment?->internal_code }}</p>
                    <p class="mt-0.5 truncate text-[10px] text-slate-500">{{ $row->equipment?->name }}</p>
                </td>
                <td class="whitespace-nowrap tabular-nums">
                    <span>{{ optional($row->reviewed_at)->format('d/m/y') }}</span>
                    <span class="text-slate-400"> → </span>
                    <span>{{ optional($row->due_at)->format('d/m/y') }}</span>
                </td>
                <td class="max-w-[10rem] truncate">{{ $row->result }}</td>
                <td class="whitespace-nowrap"><x-platform.status-badge :value="$row->status" size="xs" /></td>
                <td class="!px-1.5 !py-1">
                    <x-mechanics.row-actions
                        :edit="auth()->user()->can('revisiones.crear') ? 'openEdit('.$row->id.')' : null"
                        :delete="auth()->user()->can('mecanica.eliminar') ? 'deleteRow('.$row->id.')' : null"
                        delete-confirm="Eliminar revision?"
                    />
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="!py-5 text-center text-[11px] text-slate-500">Sin registros.</td></tr>
        @endforelse
    </x-platform.compact-table>

    <x-mechanics.pagination>{{ $rows->links() }}</x-mechanics.pagination>

    <x-platform.modal compact :show="$showFormModal" max-width="max-w-2xl">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ $editingId ? 'Editar' : 'Nueva' }} revision</h2>
            <flux:button variant="ghost" size="sm" wire:click="close">Cerrar</flux:button>
        </div>
        <div class="mt-4 grid gap-3 md:grid-cols-2">
            <div class="md:col-span-2">
                <x-mechanics.equipment-select
                    label="Equipo"
                    :options="$equipmentOptions"
                    :selected-value="$fleet_equipment_id"
                    :error="$errors->first('fleet_equipment_id')"
                />
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Fecha revision</label>
                <input type="date" wire:model="reviewed_at" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" />
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Vencimiento</label>
                <input type="date" wire:model.live="due_at" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" />
            </div>
            <div class="md:col-span-2">
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Resultado</label>
                <input wire:model.live="result" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Ej. Aprobado u Observado..." />
                <p class="mt-1 text-[10px] text-slate-500">Si el resultado menciona Observado, la revision se marca como observada.</p>
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Centro</label>
                <input wire:model="inspection_center" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" />
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Responsable</label>
                <select wire:model="responsible_user_id" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                    <option value="">—</option>
                    @foreach ($responsibleUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Observaciones</label>
                <textarea wire:model="observations" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950"></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Certificado (PDF/imagen)</label>
                <input type="file" wire:model="certificate_files" multiple class="mt-1 w-full text-xs" />
            </div>
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <flux:button variant="outline" size="sm" wire:click="close">Cancelar</flux:button>
            <flux:button variant="primary" size="sm" wire:click="save">Guardar</flux:button>
        </div>
    </x-platform.modal>
</div>
