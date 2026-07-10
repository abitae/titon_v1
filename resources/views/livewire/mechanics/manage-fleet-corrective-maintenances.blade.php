<div class="space-y-4">
    <x-mechanics.page-header :title="$title">
        @can('mantenimientos.crear')
            <flux:button variant="primary" size="sm" icon="plus" wire:click="openCreate">Registrar falla</flux:button>
        @endcan
    </x-mechanics.page-header>

    <x-mechanics.filter-strip>
        <div class="min-w-[10rem] flex-1">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Equipo, taller o descripcion" />
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

    <x-platform.compact-table dense :headers="['Equipo', 'Fecha falla', 'Taller', 'Costo', 'Estado', '']">
        @forelse ($rows as $row)
            <tr wire:key="corr-{{ $row->id }}">
                <td class="whitespace-nowrap font-medium text-slate-950 dark:text-white">{{ $row->equipment?->internal_code }}</td>
                <td class="whitespace-nowrap tabular-nums">{{ optional($row->failure_at)->format('d/m/y H:i') }}</td>
                <td class="max-w-[10rem] truncate">{{ $row->supplier_workshop ?? '—' }}</td>
                <td class="whitespace-nowrap tabular-nums">{{ $row->real_cost ? 'S/ '.number_format((float) $row->real_cost, 2) : '—' }}</td>
                <td class="whitespace-nowrap"><x-platform.status-badge :value="$row->status" size="xs" /></td>
                <td class="!px-1.5 !py-1">
                    <x-mechanics.row-actions
                        :edit="auth()->user()->can('mantenimientos.crear') ? 'openEdit('.$row->id.')' : null"
                        :delete="auth()->user()->can('mecanica.eliminar') ? 'deleteRow('.$row->id.')' : null"
                    />
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="!py-5 text-center text-[11px] text-slate-500">Sin registros.</td></tr>
        @endforelse
    </x-platform.compact-table>

    <x-mechanics.pagination>{{ $rows->links() }}</x-mechanics.pagination>

    <x-platform.modal compact :show="$showFormModal" max-width="max-w-2xl">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-slate-950 dark:text-white">Mantenimiento correctivo</h2>
            <flux:button variant="ghost" size="sm" wire:click="close">Cerrar</flux:button>
        </div>
        <div class="mt-4 grid gap-3">
            <x-mechanics.equipment-select
                :options="$equipmentOptions"
                :selected-value="$fleet_equipment_id"
                :error="$errors->first('fleet_equipment_id')"
            />
            <input type="datetime-local" wire:model="failure_at" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" />
            <textarea wire:model="failure_description" rows="2" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Descripcion falla"></textarea>
            <textarea wire:model="diagnosis" rows="2" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Diagnostico"></textarea>
            <input wire:model="supplier_workshop" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Proveedor / taller" />
            <div class="grid grid-cols-2 gap-2">
                <input wire:model="estimated_cost" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Costo estim." />
                <input wire:model="real_cost" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Costo real" />
            </div>
            <select wire:model="status" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                @foreach ($statuses as $s)
                    <option value="{{ $s->value() }}">{{ $s->label() }}</option>
                @endforeach
            </select>
            <select wire:model="responsible_user_id" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                <option value="">Responsable</option>
                @foreach ($responsibleUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
            <textarea wire:model="observations" rows="2" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950"></textarea>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div><span class="text-[10px] uppercase text-slate-500">Fotos falla</span><input type="file" wire:model="failure_photos" multiple class="mt-1 w-full" /></div>
                <div><span class="text-[10px] uppercase text-slate-500">Archivos</span><input type="file" wire:model="documents" multiple class="mt-1 w-full" /></div>
            </div>
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <flux:button variant="outline" size="sm" wire:click="close">Cancelar</flux:button>
            <flux:button variant="primary" size="sm" wire:click="save">Guardar</flux:button>
        </div>
    </x-platform.modal>
</div>
