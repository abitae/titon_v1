<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase text-cyan-700 dark:text-cyan-400">Mecanica</p>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ $title }}</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <a wire:navigate href="{{ route('modules.mechanics') }}" class="rounded-xl border px-2 py-1 text-sm dark:border-slate-600">Dashboard</a>
            @can('mantenimientos.crear')
                <button type="button" wire:click="openCreate" class="rounded-xl bg-slate-950 px-4 py-2 text-sm text-white dark:bg-cyan-500 dark:text-slate-950">Registrar falla</button>
            @endcan
        </div>
    </div>

    <x-platform.compact-table :headers="['Equipo', 'Fecha falla', 'Taller', 'Costo real', '']">
        @forelse ($rows as $row)
            <tr wire:key="corr-{{ $row->id }}">
                <td class="font-medium text-slate-950 dark:text-white">{{ $row->equipment?->internal_code }}</td>
                <td class="whitespace-nowrap">{{ optional($row->failure_at)->format('d/m/Y H:i') }}</td>
                <td>{{ $row->supplier_workshop ?? '—' }}</td>
                <td class="tabular-nums">{{ $row->real_cost ?? '—' }}</td>
                <td class="!px-1.5 !py-1 text-end">
                    @can('mantenimientos.crear')
                        <button type="button" wire:click="openEdit({{ $row->id }})" class="text-[11px] font-medium text-cyan-700">Editar</button>
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
            <div class="w-full max-w-2xl rounded-3xl border bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="flex justify-between">
                    <h2 class="text-lg font-semibold dark:text-white">Correctivo</h2>
                    <button wire:click="close" type="button">Cerrar</button>
                </div>
                <div class="mt-4 grid gap-3">
                    <select wire:model="fleet_equipment_id" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                        @foreach ($equipments as $equipment)
                            <option value="{{ $equipment->id }}">{{ $equipment->internal_code }} · {{ $equipment->name }}</option>
                        @endforeach
                    </select>
                    <input type="datetime-local" wire:model="failure_at" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" />
                    <textarea wire:model="failure_description" rows="2" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Descripcion falla"></textarea>
                    <textarea wire:model="diagnosis" rows="2" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Diagnostico"></textarea>
                    <input wire:model="supplier_workshop" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Proveedor / taller" />
                    <div class="grid grid-cols-2 gap-2">
                        <input wire:model="estimated_cost" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Costo estim." />
                        <input wire:model="real_cost" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Costo real" />
                    </div>
                    <select wire:model="status" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value() }}">{{ $s->label() }}</option>
                        @endforeach
                    </select>
                    <select wire:model="responsible_user_id" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                        <option value="">Responsable</option>
                        @foreach ($responsibleUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <textarea wire:model="observations" rows="2" class="rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950"></textarea>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div><span class="text-xs text-slate-500">Fotos falla</span><input type="file" wire:model="failure_photos" multiple class="mt-1 w-full" /></div>
                        <div><span class="text-xs text-slate-500">Archivos</span><input type="file" wire:model="documents" multiple class="mt-1 w-full" /></div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="close" type="button" class="rounded-xl border px-4 py-2 text-sm">Cancelar</button>
                    <button wire:click="save" type="button" class="rounded-xl bg-slate-950 px-4 py-2 text-sm text-white dark:bg-cyan-500 dark:text-slate-950">Guardar</button>
                </div>
            </div>
        </div>
    @endif
</div>
