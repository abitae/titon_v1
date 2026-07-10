<div class="space-y-4">
    <x-mechanics.page-header :title="$title">
        @can('mantenimientos.crear')
            <flux:button variant="primary" size="sm" icon="plus" wire:click="openCreate">Programar</flux:button>
        @endcan
    </x-mechanics.page-header>

    <x-mechanics.filter-strip>
        <div class="min-w-[10rem] flex-1">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Equipo o tipo de mantenimiento" />
        </div>
        <div class="w-36">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</label>
            <select wire:model.live="statusFilter" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value() }}">{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    </x-mechanics.filter-strip>

    <x-platform.compact-table dense :headers="['Equipo', 'Tipo / fecha', 'Prioridad', 'Estado', '']">
        @forelse ($rows as $row)
            <tr wire:key="prev-{{ $row->id }}">
                <td class="whitespace-nowrap font-medium text-slate-950 dark:text-white">{{ $row->equipment?->internal_code }}</td>
                <td>
                    <p class="font-medium leading-tight">{{ $row->maintenance_type }}</p>
                    <p class="mt-0.5 text-[10px] text-slate-500">{{ optional($row->scheduled_date)->format('d/m/y') ?? '—' }}</p>
                </td>
                <td class="whitespace-nowrap"><x-platform.status-badge :value="$row->priority" size="xs" /></td>
                <td class="whitespace-nowrap"><x-platform.status-badge :value="$row->status" size="xs" /></td>
                <td class="!px-1.5 !py-1">
                    <x-mechanics.row-actions
                        :edit="auth()->user()->can('mantenimientos.crear') ? 'openEdit('.$row->id.')' : null"
                        :delete="auth()->user()->can('mecanica.eliminar') ? 'deleteRow('.$row->id.')' : null"
                    />
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="!py-5 text-center text-[11px] text-slate-500">Sin registros.</td></tr>
        @endforelse
    </x-platform.compact-table>

    <x-mechanics.pagination>{{ $rows->links() }}</x-mechanics.pagination>

    <x-platform.modal compact :show="$showFormModal" max-width="max-w-xl">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-slate-950 dark:text-white">Mantenimiento preventivo</h2>
            <flux:button variant="ghost" size="sm" wire:click="close">Cerrar</flux:button>
        </div>
        <div class="mt-4 grid gap-3">
            <x-mechanics.equipment-select
                label="Equipo"
                :options="$equipmentOptions"
                :selected-value="$fleet_equipment_id"
                :error="$errors->first('fleet_equipment_id')"
            />
            <input wire:model="maintenance_type" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Tipo de mantenimiento" />
            <input type="date" wire:model="scheduled_date" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" />
            <div class="grid grid-cols-2 gap-2">
                <input wire:model="scheduled_odometer" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Km prog." />
                <input wire:model="scheduled_hour_meter" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Horometro prog." />
            </div>
            <div class="grid grid-cols-2 gap-2">
                <select wire:model="priority" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                    @foreach ($priorities as $p)
                        <option value="{{ $p->value() }}">{{ $p->label() }}</option>
                    @endforeach
                </select>
                <select wire:model="status" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value() }}">{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
            <input wire:model="cost" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Costo al cierre S/" />
            <select wire:model="responsible_user_id" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                <option value="">Responsable</option>
                @foreach ($responsibleUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
            <textarea wire:model="observations" rows="2" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Observaciones"></textarea>
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <flux:button variant="outline" size="sm" wire:click="close">Cancelar</flux:button>
            <flux:button variant="primary" size="sm" wire:click="save">Guardar</flux:button>
        </div>
    </x-platform.modal>
</div>
