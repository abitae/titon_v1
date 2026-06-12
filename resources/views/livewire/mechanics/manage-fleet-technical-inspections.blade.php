<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700 dark:text-cyan-400">Mecanica</p>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ $title }}</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <a wire:navigate href="{{ route('modules.mechanics') }}" class="rounded-xl border px-2 py-1 text-sm dark:border-slate-600">Dashboard</a>
            @can('revisiones.crear')
                <button type="button" wire:click="openCreate" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white dark:bg-cyan-500 dark:text-slate-950">Nueva revision</button>
            @endcan
        </div>
    </div>

    <x-platform.compact-table :headers="['Equipo', 'Revisión / venc.', 'Resultado', 'Estado', '']">
        @forelse ($rows as $row)
            <tr wire:key="insp-{{ $row->id }}">
                <td class="font-medium text-slate-950 dark:text-white">{{ $row->equipment?->internal_code }} · {{ $row->equipment?->name }}</td>
                <td class="whitespace-nowrap">{{ optional($row->reviewed_at)->format('d/m/Y') }} → {{ optional($row->due_at)->format('d/m/Y') }}</td>
                <td>{{ $row->result }}</td>
                <td>{{ str_replace('_', ' ', $row->status) }}</td>
                <td class="!px-1.5 !py-1 text-end">
                    @can('revisiones.crear')
                        <button type="button" wire:click="openEdit({{ $row->id }})" class="text-[11px] font-medium text-cyan-700 dark:text-cyan-400">Editar</button>
                    @endcan
                    @can('mecanica.eliminar')
                        <button type="button" wire:click="deleteRow({{ $row->id }})" wire:confirm="Eliminar revision?" class="ms-2 text-[11px] font-medium text-rose-600">Eliminar</button>
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
            <div class="w-full max-w-2xl rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="flex justify-between gap-4">
                    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $editingId ? 'Editar' : 'Nueva' }} revision</h2>
                    <button wire:click="close" type="button" class="text-slate-500">Cerrar</button>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="text-xs uppercase text-slate-500">Equipo</label>
                        <select wire:model.live="fleet_equipment_id" class="mt-1 w-full rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                            @foreach ($equipments as $equipment)
                                <option value="{{ $equipment->id }}">{{ $equipment->internal_code }} · {{ $equipment->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs uppercase text-slate-500">Fecha revision</label>
                        <input type="date" wire:model="reviewed_at" class="mt-1 w-full rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" />
                    </div>
                    <div>
                        <label class="text-xs uppercase text-slate-500">Vencimiento</label>
                        <input type="date" wire:model.live="due_at" class="mt-1 w-full rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs uppercase text-slate-500">Resultado</label>
                        <input wire:model.live="result" class="mt-1 w-full rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" placeholder="Ej. Aprobado u Observado..." />
                        <p class="mt-1 text-[11px] text-slate-500">Si el resultado menciona Observado, la revision se marca como observada.</p>
                    </div>
                    <div>
                        <label class="text-xs uppercase text-slate-500">Centro</label>
                        <input wire:model="inspection_center" class="mt-1 w-full rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950" />
                    </div>
                    <div>
                        <label class="text-xs uppercase text-slate-500">Responsable</label>
                        <select wire:model="responsible_user_id" class="mt-1 w-full rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950">
                            <option value="">–</option>
                            @foreach ($responsibleUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs uppercase text-slate-500">Observaciones</label>
                        <textarea wire:model="observations" rows="3" class="mt-1 w-full rounded-xl border px-2 py-1 dark:border-slate-700 dark:bg-slate-950"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs uppercase text-slate-500">Certificado (PDF/imagen)</label>
                        <input type="file" wire:model="certificate_files" multiple class="mt-1 w-full text-sm" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="close" type="button" class="rounded-xl border px-4 py-2 text-sm dark:border-slate-600">Cancelar</button>
                    <button wire:click="save" type="button" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white dark:bg-cyan-500 dark:text-slate-950">Guardar</button>
                </div>
            </div>
        </div>
    @endif
</div>
