<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Cronograma de pagos</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $supplierContract->contract_number }} · {{ $supplierContract->supplier?->business_name ?? 'Sin proveedor' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('modules.payments') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Pagos</a>
            <button type="button" wire:click="openCreateModal" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Nueva cuota</button>
        </div>
    </div>

    <x-platform.compact-table :headers="['Cuota', 'Descripcion', 'Vencimiento', 'Programado', 'Pagado', 'Saldo', 'Estado', 'Acciones']">
        @forelse ($schedules as $schedule)
            <tr class="text-sm text-slate-700 dark:text-slate-200" wire:key="schedule-row-{{ $schedule->id }}">
                <td class="px-6 py-4 font-medium text-slate-950 dark:text-white">{{ $schedule->installment_number }}</td>
                <td class="px-6 py-4">{{ $schedule->description }}</td>
                <td class="px-6 py-4">{{ $schedule->due_date?->format('d/m/Y') }}</td>
                <td class="px-6 py-4">{{ $supplierContract->currency }} {{ number_format((float) $schedule->scheduled_amount, 2) }}</td>
                <td class="px-6 py-4">{{ $supplierContract->currency }} {{ number_format((float) $schedule->paid_amount, 2) }}</td>
                <td class="px-6 py-4">{{ $supplierContract->currency }} {{ number_format((float) $schedule->balance, 2) }}</td>
                <td class="px-6 py-4"><x-platform.status-badge :value="$schedule->status" /></td>
                <td class="px-6 py-4">
                    <button type="button" wire:click="openEditModal({{ $schedule->id }})" class="rounded-lg px-2 py-1 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200">Editar</button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No hay cuotas registradas para este contrato.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <x-platform.modal :show="$showFormModal" max-width="max-w-3xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $editingScheduleId ? 'Editar cuota' : 'Nueva cuota' }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Define hitos de pago y controla vencimientos del contrato.</p>
            </div>
            <button type="button" wire:click="closeModal" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Numero de cuota</label>
                <input wire:model="installment_number" type="number" min="1" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('installment_number') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Vencimiento</label>
                <input wire:model="due_date" type="date" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('due_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Descripcion</label>
                <input wire:model="description" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('description') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Monto programado</label>
                <input wire:model="scheduled_amount" type="number" step="0.01" min="0.01" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('scheduled_amount') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Estado</label>
                <select wire:model="status" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" wire:click="closeModal" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancelar</button>
            <button type="button" wire:click="saveSchedule" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Guardar cuota</button>
        </div>
    </x-platform.modal>
</div>
