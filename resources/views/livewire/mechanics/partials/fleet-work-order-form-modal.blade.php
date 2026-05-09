@if ($showFormModal)
    <div class="fixed inset-0 z-50 flex justify-center overflow-y-auto bg-slate-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-3xl rounded-3xl border bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex justify-between">
                <flux:heading size="lg">Orden de trabajo</flux:heading>
                <flux:button variant="ghost" size="sm" wire:click="close" type="button">Cerrar</flux:button>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <flux:field>
                    <flux:label>Código OT</flux:label>
                    <flux:input wire:model="code" />
                </flux:field>
                <flux:field>
                    <flux:label>Equipo</flux:label>
                    <select wire:model.live="fleet_equipment_id" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                        @foreach ($equipments as $equipment)
                            <option value="{{ $equipment->id }}">{{ $equipment->internal_code }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>Obra</flux:label>
                    <select wire:model="work_project_id" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                        <option value="">—</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->code }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>Tipo</flux:label>
                    <select wire:model="type" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                        @foreach ($types as $t)
                            <option value="{{ $t->value() }}">{{ $t->label() }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>Prioridad</flux:label>
                    <select wire:model="priority" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                        @foreach ($priorities as $p)
                            <option value="{{ $p->value() }}">{{ $p->label() }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>Estado</flux:label>
                    <select wire:model="status" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value() }}">{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>Emisión</flux:label>
                    <flux:input type="date" wire:model="issued_at" />
                </flux:field>
                <flux:field>
                    <flux:label>Programado</flux:label>
                    <flux:input type="date" wire:model="scheduled_date" />
                </flux:field>
                <div class="md:col-span-2">
                    <flux:field>
                        <flux:label>Descripción del trabajo</flux:label>
                        <flux:textarea wire:model="work_description" rows="2" />
                    </flux:field>
                </div>
                <flux:field>
                    <flux:label>Diagnóstico</flux:label>
                    <flux:textarea wire:model="diagnosis" rows="2" />
                </flux:field>
                <flux:field>
                    <flux:label>Repuestos (texto)</flux:label>
                    <flux:textarea wire:model="parts_used_description" rows="2" />
                </flux:field>
                <flux:field>
                    <flux:label>Mano de obra S/</flux:label>
                    <flux:input wire:model="labor_cost" />
                </flux:field>
                <flux:field>
                    <flux:label>Repuestos S/ (editable; salidas suman en kardex)</flux:label>
                    <flux:input wire:model="spare_parts_cost" />
                </flux:field>
                <flux:field>
                    <flux:label>Responsable</flux:label>
                    <select wire:model="responsible_user_id" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                        <option value="">—</option>
                        @foreach ($responsibleUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </flux:field>
                <div class="md:col-span-2 grid gap-2 md:grid-cols-3">
                    <flux:field>
                        <flux:label>Vinc. preventivo</flux:label>
                        <select wire:model="fleet_preventive_maintenance_id" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2 py-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                            <option value="">—</option>
                            @foreach ($preventives as $p)
                                <option value="{{ $p->id }}">#{{ $p->id }} {{ $p->maintenance_type }}</option>
                            @endforeach
                        </select>
                    </flux:field>
                    <flux:field>
                        <flux:label>Vinc. correctivo</flux:label>
                        <select wire:model="fleet_corrective_maintenance_id" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2 py-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                            <option value="">—</option>
                            @foreach ($correctives as $c)
                                <option value="{{ $c->id }}">#{{ $c->id }}</option>
                            @endforeach
                        </select>
                    </flux:field>
                    <flux:field>
                        <flux:label>Vinc. revisión</flux:label>
                        <select wire:model="fleet_technical_inspection_id" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-2 py-2 text-xs dark:border-slate-700 dark:bg-slate-950">
                            <option value="">—</option>
                            @foreach ($inspections as $i)
                                <option value="{{ $i->id }}">#{{ $i->id }} {{ optional($i->due_at)->format('d/m/Y') }}</option>
                            @endforeach
                        </select>
                    </flux:field>
                </div>
                <div class="md:col-span-2">
                    <flux:field>
                        <flux:label>Adjuntos (informes)</flux:label>
                        <input type="file" wire:model="attachments" multiple class="mt-1 w-full text-sm" />
                    </flux:field>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="close" type="button">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="save" type="button">Guardar</flux:button>
            </div>
        </div>
    </div>
@endif
