<div class="space-y-4">
    <x-mechanics.page-header :title="$title" description="Kardex basico: entradas suman stock, salidas restan y actualizan costo en la OT.">
        @can('mecanica.crear')
            <flux:button variant="primary" size="sm" icon="plus" wire:click="openPartCreate">Nuevo repuesto</flux:button>
        @endcan
    </x-mechanics.page-header>

    <x-mechanics.filter-strip>
        <div class="min-w-[10rem] flex-1">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Codigo, nombre o categoria" />
        </div>
    </x-mechanics.filter-strip>

    <x-platform.compact-table dense :headers="['Codigo', 'Nombre', 'Stock / min.', 'Costo unit.', '']">
        @forelse ($parts as $part)
            <tr wire:key="sp-{{ $part->id }}">
                <td class="whitespace-nowrap font-medium text-slate-950 dark:text-white">{{ $part->code }}</td>
                <td class="max-w-[14rem]">
                    <p class="truncate font-medium leading-tight">{{ $part->name }}</p>
                    <p class="mt-0.5 truncate text-[10px] text-slate-500">{{ $part->category ?? 'Sin categoria' }}</p>
                </td>
                <td class="whitespace-nowrap tabular-nums">
                    <span @class(['font-semibold text-amber-700 dark:text-amber-400' => $part->isBelowMinStock()])>{{ $part->stock_quantity }} {{ $part->unit }}</span>
                    <span class="text-slate-400"> / </span>{{ $part->min_stock }}
                </td>
                <td class="whitespace-nowrap tabular-nums">S/ {{ number_format((float) $part->unit_cost, 2) }}</td>
                <td class="!px-1.5 !py-1">
                    <x-mechanics.row-actions
                        :edit="auth()->user()->can('mecanica.editar') ? 'openPartEdit('.$part->id.')' : null"
                        :delete="auth()->user()->can('mecanica.eliminar') ? 'deletePart('.$part->id.')' : null"
                    />
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="!py-5 text-center text-[11px] text-slate-500">Sin repuestos.</td></tr>
        @endforelse
    </x-platform.compact-table>

    <x-mechanics.pagination>{{ $parts->links() }}</x-mechanics.pagination>

    <x-platform.modal compact :show="$showPartModal" max-width="max-w-lg">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-slate-950 dark:text-white">Repuesto</h2>
            <flux:button variant="ghost" size="sm" wire:click="closePart">Cerrar</flux:button>
        </div>
        <div class="mt-4 grid gap-3">
            <input wire:model="code" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Codigo" />
            <input wire:model="name" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Nombre" />
            <input wire:model="category" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Categoria" />
            <input wire:model="unit" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Unidad" />
            @if (! $editingPartId)
                <input wire:model="stock_quantity" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Stock inicial" />
            @endif
            <input wire:model="min_stock" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Stock minimo" />
            <input wire:model="unit_cost" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Costo unitario" />
            <select wire:model="supplier_id" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                <option value="">Proveedor</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->business_name }}</option>
                @endforeach
            </select>
            <select wire:model="warehouse_project_id" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                <option value="">Almacen / obra</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->code }}</option>
                @endforeach
            </select>
            <select wire:model="status" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <flux:button variant="outline" size="sm" wire:click="closePart">Cancelar</flux:button>
            <flux:button variant="primary" size="sm" wire:click="savePart">Guardar</flux:button>
        </div>
    </x-platform.modal>

    <x-platform.modal compact :show="$showMovementModal" max-width="max-w-md">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-slate-950 dark:text-white">Movimiento de kardex</h2>
            <flux:button variant="ghost" size="sm" wire:click="closeMovement">Cerrar</flux:button>
        </div>
        <div class="mt-4 grid gap-3">
            <select wire:model="movement_direction" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                <option value="{{ \App\Enums\FleetSparePartMovementDirection::Inbound->value() }}">Entrada</option>
                <option value="{{ \App\Enums\FleetSparePartMovementDirection::Outbound->value() }}">Salida (requiere OT)</option>
            </select>
            <input wire:model="movement_quantity" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Cantidad" />
            <input wire:model="movement_unit_cost" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Costo unit. (entrada / override)" />
            <select wire:model="movement_work_order_id" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                <option value="">Orden de trabajo (salida)</option>
                @foreach ($workOrders as $wo)
                    <option value="{{ $wo->id }}">{{ $wo->code }}</option>
                @endforeach
            </select>
            <input wire:model="movement_reference" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950" placeholder="Referencia" />
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <flux:button variant="outline" size="sm" wire:click="closeMovement">Cancelar</flux:button>
            <flux:button variant="primary" size="sm" wire:click="saveMovement">Registrar</flux:button>
        </div>
    </x-platform.modal>
</div>
