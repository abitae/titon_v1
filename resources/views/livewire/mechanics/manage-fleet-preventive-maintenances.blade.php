<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700 dark:text-cyan-400">Mecanica</p>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ $title }}</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <a wire:navigate href="{{ route('modules.mechanics') }}" class="rounded-xl border px-2 py-1 text-sm dark:border-slate-600">Dashboard</a>
            @can('mantenimientos.crear')
                <button type="button" wire:click="openCreate" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white dark:bg-cyan-500 dark:text-slate-950">Programar</button>
            @endcan
        </div>
    </div>

    <x-platform.compact-table :headers="['Equipo', 'Tipo / fecha prog.', 'Prioridad', 'Estado', '']">
        @forelse ($rows as $row)
            <tr wire:key="prev-{{ $row->id }}">
                <td class="font-medium text-slate-950 dark:text-white">{{ $row->equipment?->internal_code }}</td>
                <td>{{ $row->maintenance_type }} · {{ optional($row->scheduled_date)->format('d/m/Y') }}</td>
                <td>{{ $row->priority }}</td>
                <td>{{ str_replace('_', ' ', $row->status) }}</td>
                <td class="!px-1.5 !py-1 text-end">
                    @can('mantenimientos.crear')
                        <button type="button" wire:click="openEdit({{ $row->id }})" class="text-[11px] font-medium text-cyan-700 dark:text-cyan-400">Editar</button>
                    @endcan
                    @can('mecanica.eliminar')
                        <button type="button" wire:click="deleteRow({{ $row->id }})" wire:confirm="¿Eliminar?" class="ms-2 text-[11px] font-medium text-rose-600">Eliminar</button>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="!py-5 text-center text-[11px] text-slate-500">Sin registros.</td></tr>
        @endforelse
    </x-platform.compact-table>
    {{ $rows->links() }}

    @if ($showFormModal)
        <div class="fixed inset-0 z-50 flex justify-center overflow-y-auto bg-slate-950/60 px-4 py-8 backdrop-blur-sm">
            <div class="w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="flex justify-between">
                    <h2 class="text-lg font-semibold dark:text-white">Mantenimiento preventivo</h2>
                    <button wire:click="close" type="button">Cerrar</button>
                </div>
                <div class="mt-4 grid gap-3">
                    <div>
                        <label class="text-xs uppercase text-slate-500">Equipo</label>
                        <select wire:model="fleet_equipment_id" class="mt-1 w-full rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                            @foreach ($equipments as $equipment)
                                <option value="{{ $equipment->id }}">{{ $equipment->internal_code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input wire:model="maintenance_type" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Tipo de mantenimiento" />
                    <input type="date" wire:model="scheduled_date" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" />
                    <div class="grid grid-cols-2 gap-2">
                        <input wire:model="scheduled_odometer" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Km prog." />
                        <input wire:model="scheduled_hour_meter" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Horometro prog." />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <select wire:model="priority" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                            @foreach ($priorities as $p)
                                <option value="{{ $p->value() }}">{{ $p->label() }}</option>
                            @endforeach
                        </select>
                        <select wire:model="status" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                            @foreach ($statuses as $s)
                                <option value="{{ $s->value() }}">{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input wire:model="cost" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Costo al cierre S/" />
                    <select wire:model="responsible_user_id" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                        <option value="">Responsable</option>
                        @foreach ($responsibleUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <textarea wire:model="observations" rows="3" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Observaciones"></textarea>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="close" type="button" class="rounded-xl border px-4 py-2 text-sm">Cancelar</button>
                    <button wire:click="save" type="button" class="rounded-xl bg-slate-950 px-4 py-2 text-sm text-white dark:bg-cyan-500 dark:text-slate-950">Guardar</button>
                </div>
            </div>
        </div>
    @endif
</div>
