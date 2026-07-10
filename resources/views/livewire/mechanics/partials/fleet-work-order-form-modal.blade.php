<x-platform.modal compact :show="$showFormModal" max-width="max-w-2xl">
    <div class="flex items-center justify-between gap-2">
        <div class="min-w-0">
            <h2 class="truncate text-base font-semibold text-slate-950 dark:text-white">
                {{ $editingId ? 'Editar OT' : 'Nueva orden de trabajo' }}
            </h2>
            @if ($editingId && filled($code))
                <p class="mt-0.5 text-[10px] font-medium text-slate-500">{{ $code }}</p>
            @endif
        </div>
        <flux:button variant="ghost" size="sm" wire:click="close" type="button">Cerrar</flux:button>
    </div>

    <div class="mt-3 grid gap-2.5 md:grid-cols-3">
        @if ($editingId)
            <div>
                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Código OT</label>
                <input wire:model="code" readonly class="mt-1 h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300" />
            </div>
        @else
            <div class="md:col-span-3 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-2 py-1.5 text-[10px] text-slate-600 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300">
                El código se asigna automáticamente al guardar.
            </div>
        @endif

        <div @class(['md:col-span-2' => ! $editingId])>
            <x-mechanics.equipment-select
                label="Equipo"
                :options="$equipmentFormOptions"
                :selected-value="$fleet_equipment_id"
                placeholder="Buscar por código o nombre..."
                :error="$errors->first('fleet_equipment_id')"
            />
        </div>

        <div>
            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Obra</label>
            <select wire:model="work_project_id" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Sin obra</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->code }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Tipo</label>
            <select wire:model="type" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                @foreach ($types as $t)
                    <option value="{{ $t->value() }}">{{ $t->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Prioridad</label>
            <select wire:model="priority" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                @foreach ($priorities as $p)
                    <option value="{{ $p->value() }}">{{ $p->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Estado</label>
            <select wire:model="status" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                @foreach ($statuses as $s)
                    <option value="{{ $s->value() }}">{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Emisión</label>
            <input type="date" wire:model="issued_at" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        </div>

        <div>
            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Programado</label>
            <input type="date" wire:model="scheduled_date" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        </div>

        <div>
            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Responsable</label>
            <select wire:model="responsible_user_id" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Sin asignar</option>
                @foreach ($responsibleUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">M. obra S/</label>
            <input wire:model="labor_cost" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="0.00" />
        </div>

        <div>
            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Repuestos S/</label>
            <input wire:model="spare_parts_cost" class="mt-1 h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="0.00" />
        </div>
    </div>

    <details class="mt-2.5 rounded-lg border border-slate-200 dark:border-slate-800">
        <summary class="cursor-pointer px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-500 select-none">Detalle y vinculaciones</summary>
        <div class="space-y-2 border-t border-slate-200 px-2.5 py-2 dark:border-slate-800">
            <textarea wire:model="work_description" rows="2" class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Descripción del trabajo"></textarea>
            <div class="grid gap-2 md:grid-cols-2">
                <textarea wire:model="diagnosis" rows="2" class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Diagnóstico"></textarea>
                <textarea wire:model="parts_used_description" rows="2" class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Repuestos utilizados (texto)"></textarea>
            </div>
            <div class="grid gap-2 md:grid-cols-3">
                <select wire:model="fleet_preventive_maintenance_id" class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-[11px] dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Vinc. preventivo</option>
                    @foreach ($preventives as $p)
                        <option value="{{ $p->id }}">#{{ $p->id }} {{ $p->maintenance_type }}</option>
                    @endforeach
                </select>
                <select wire:model="fleet_corrective_maintenance_id" class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-[11px] dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Vinc. correctivo</option>
                    @foreach ($correctives as $c)
                        <option value="{{ $c->id }}">#{{ $c->id }}</option>
                    @endforeach
                </select>
                <select wire:model="fleet_technical_inspection_id" class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-[11px] dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Vinc. revisión</option>
                    @foreach ($inspections as $i)
                        <option value="{{ $i->id }}">#{{ $i->id }} {{ optional($i->due_at)->format('d/m/y') }}</option>
                    @endforeach
                </select>
            </div>
            <input type="file" wire:model="attachments" multiple class="block w-full text-[11px] text-slate-600 file:mr-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-[11px] file:font-medium dark:text-slate-400 dark:file:bg-slate-800" />
        </div>
    </details>

    <div class="mt-3 flex justify-end gap-2 border-t border-slate-200 pt-3 dark:border-slate-800">
        <flux:button variant="outline" size="sm" wire:click="close" type="button">Cancelar</flux:button>
        <flux:button variant="primary" size="sm" wire:click="save" type="button">Guardar</flux:button>
    </div>
</x-platform.modal>
