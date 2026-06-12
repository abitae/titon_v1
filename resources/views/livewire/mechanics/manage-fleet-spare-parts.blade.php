<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase text-cyan-700 dark:text-cyan-400">Mecanica</p>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ $title }}</h1>
            <p class="text-sm text-slate-600 dark:text-slate-300">Kardex basico: entradas suman stock, salidas restan y actualizan costo de repuestos en la OT.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a wire:navigate href="{{ route('modules.mechanics') }}" class="rounded-xl border px-2 py-1 text-sm dark:border-slate-600">Dashboard</a>
            @can('mecanica.crear')
                <button type="button" wire:click="openPartCreate" class="rounded-xl bg-slate-950 px-4 py-2 text-sm text-white dark:bg-cyan-500 dark:text-slate-950">Nuevo repuesto</button>
            @endcan
        </div>
    </div>

    <x-platform.compact-table :headers="['Código', 'Nombre', 'Stock / mín.', 'Costo unit.', '']">
        @forelse ($parts as $part)
            <tr wire:key="sp-{{ $part->id }}">
                <td class="font-medium text-slate-950 dark:text-white">{{ $part->code }}</td>
                <td>{{ $part->name }}</td>
                <td>
                    <span @class(['font-semibold text-amber-700 dark:text-amber-400' => $part->isBelowMinStock()])>{{ $part->stock_quantity }} {{ $part->unit }}</span>
                    / {{ $part->min_stock }}
                </td>
                <td class="tabular-nums">S/ {{ number_format((float) $part->unit_cost, 4) }}</td>
                <td class="!px-1.5 !py-1 space-x-2 text-end">
                    @can('mecanica.crear')
                        <button type="button" wire:click="openMovement({{ $part->id }})" class="text-[11px] font-medium text-cyan-700">Movimiento</button>
                    @endcan
                    @can('mecanica.editar')
                        <button type="button" wire:click="openPartEdit({{ $part->id }})" class="text-[11px] font-medium">Editar</button>
                    @endcan
                    @can('mecanica.eliminar')
                        <button type="button" wire:click="deletePart({{ $part->id }})" wire:confirm="¿Eliminar?" class="text-[11px] font-medium text-rose-600">Eliminar</button>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="!py-5 text-center text-[11px] text-slate-500">Sin repuestos.</td></tr>
        @endforelse
    </x-platform.compact-table>
    {{ $parts->links() }}

    @if ($showPartModal)
        <div class="fixed inset-0 z-50 flex justify-center overflow-y-auto bg-slate-950/60 px-4 py-8 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-3xl border bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex justify-between">
                    <h2 class="text-lg font-semibold dark:text-white">Repuesto</h2>
                    <button wire:click="closePart" type="button">Cerrar</button>
                </div>
                <div class="mt-4 grid gap-3">
                    <input wire:model="code" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Codigo" />
                    <input wire:model="name" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Nombre" />
                    <input wire:model="category" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Categoria" />
                    <input wire:model="unit" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Unidad" />
                    @if (! $editingPartId)
                        <input wire:model="stock_quantity" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Stock inicial" />
                    @endif
                    <input wire:model="min_stock" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Stock minimo" />
                    <input wire:model="unit_cost" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Costo unitario" />
                    <select wire:model="supplier_id" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                        <option value="">Proveedor</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->business_name }}</option>
                        @endforeach
                    </select>
                    <select wire:model="warehouse_project_id" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                        <option value="">Almacen / obra</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->code }}</option>
                        @endforeach
                    </select>
                    <select wire:model="status" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="closePart" type="button" class="rounded-xl border px-4 py-2 text-sm">Cancelar</button>
                    <button wire:click="savePart" type="button" class="rounded-xl bg-slate-950 px-4 py-2 text-sm text-white dark:bg-cyan-500 dark:text-slate-950">Guardar</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showMovementModal)
        <div class="fixed inset-0 z-50 flex justify-center overflow-y-auto bg-slate-950/60 px-4 py-8 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-3xl border bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex justify-between">
                    <h2 class="text-lg font-semibold dark:text-white">Movimiento de kardex</h2>
                    <button wire:click="closeMovement" type="button">Cerrar</button>
                </div>
                <div class="mt-4 grid gap-3">
                    <select wire:model="movement_direction" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                        <option value="{{ \App\Enums\FleetSparePartMovementDirection::Inbound->value() }}">Entrada</option>
                        <option value="{{ \App\Enums\FleetSparePartMovementDirection::Outbound->value() }}">Salida (requiere OT)</option>
                    </select>
                    <input wire:model="movement_quantity" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Cantidad" />
                    <input wire:model="movement_unit_cost" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Costo unit. (entrada / override)" />
                    <select wire:model="movement_work_order_id" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                        <option value="">Orden de trabajo (salida)</option>
                        @foreach ($workOrders as $wo)
                            <option value="{{ $wo->id }}">{{ $wo->code }}</option>
                        @endforeach
                    </select>
                    <input wire:model="movement_reference" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Referencia" />
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="closeMovement" type="button" class="rounded-xl border px-4 py-2 text-sm">Cancelar</button>
                    <button wire:click="saveMovement" type="button" class="rounded-xl bg-slate-950 px-4 py-2 text-sm text-white dark:bg-cyan-500 dark:text-slate-950">Registrar</button>
                </div>
            </div>
        </div>
    @endif
</div>
