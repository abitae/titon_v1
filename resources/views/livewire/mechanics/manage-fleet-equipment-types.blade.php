<div class="space-y-4">
    <x-mechanics.page-header :title="$title" description="Catalogo de tipos para clasificar equipos y maquinaria.">
        <flux:button variant="outline" size="sm" href="{{ route('mechanics.equipments') }}" wire:navigate>Equipos</flux:button>
        @can('equipos.crear')
            <flux:button variant="primary" size="sm" icon="plus" wire:click="openCreate">Nuevo tipo</flux:button>
        @endcan
    </x-mechanics.page-header>

    <x-mechanics.filter-strip>
        <div class="min-w-[10rem] flex-1">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Nombre o codigo" />
        </div>
        <div class="w-36">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</label>
            <select wire:model.live="activeFilter" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="all">Todos</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>
        </div>
    </x-mechanics.filter-strip>

    <x-platform.compact-table dense :headers="['Nombre', 'Codigo', 'Estado', 'Orden', '']">
        @forelse ($types as $type)
            <tr wire:key="eq-type-{{ $type->id }}">
                <td class="max-w-[14rem]">
                    <p class="truncate font-medium leading-tight text-slate-950 dark:text-white">{{ $type->name }}</p>
                    <p class="mt-0.5 truncate text-[10px] text-slate-500">{{ $type->description ?: 'Sin descripcion' }}</p>
                </td>
                <td class="whitespace-nowrap">{{ $type->code ?: '—' }}</td>
                <td class="whitespace-nowrap"><x-platform.status-badge :value="$type->is_active ? 'activo' : 'inactivo'" size="xs" /></td>
                <td class="whitespace-nowrap tabular-nums">{{ $type->sort_order }}</td>
                <td class="!px-1.5 !py-1">
                    <x-mechanics.row-actions
                        :edit="auth()->user()->can('equipos.editar') ? 'openEdit('.$type->id.')' : null"
                        :delete="auth()->user()->can('equipos.eliminar') ? 'delete('.$type->id.')' : null"
                        delete-confirm="Eliminar tipo de equipo?"
                    />
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="!py-5 text-center text-[11px] text-slate-500">Sin tipos registrados.</td></tr>
        @endforelse
    </x-platform.compact-table>

    <x-mechanics.pagination>{{ $types->links() }}</x-mechanics.pagination>

    <x-platform.modal compact :show="$showFormModal" max-width="max-w-lg">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ $editingId ? 'Editar tipo' : 'Nuevo tipo de equipo' }}</h2>
            <flux:button variant="ghost" size="sm" wire:click="close">Cerrar</flux:button>
        </div>
        <div class="mt-4 grid gap-3">
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Nombre</label>
                <input wire:model="name" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 @error('name') border-rose-500 @enderror" />
                @error('name') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Codigo (opcional)</label>
                <input wire:model="code" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 @error('code') border-rose-500 @enderror" />
                @error('code') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Descripcion</label>
                <textarea wire:model="description" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 @error('description') border-rose-500 @enderror"></textarea>
                @error('description') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Orden</label>
                    <input type="number" min="0" wire:model="sort_order" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 @error('sort_order') border-rose-500 @enderror" />
                    @error('sort_order') <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-200">
                        <input type="checkbox" wire:model="is_active" class="rounded" />
                        Activo
                    </label>
                </div>
            </div>
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <flux:button variant="outline" size="sm" wire:click="close">Cancelar</flux:button>
            <flux:button variant="primary" size="sm" wire:click="save">Guardar</flux:button>
        </div>
    </x-platform.modal>
</div>
