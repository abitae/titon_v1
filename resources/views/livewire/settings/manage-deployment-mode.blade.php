<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Produccion</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                Alterna el sistema entre datos demo de desarrollo y una base limpia para produccion.
            </p>
        </div>

        <span class="inline-flex w-fit items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $deploymentMode === 'production' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
            {{ $deploymentMode === 'production' ? 'Produccion' : 'Desarrollo' }}
        </span>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            'Usuarios demo' => $summary['users'] ?? 0,
            'Obras' => $summary['projects'] ?? 0,
            'Proveedores' => $summary['suppliers'] ?? 0,
            'Requerimientos' => $summary['requirements'] ?? 0,
            'Ordenes' => $summary['orders'] ?? 0,
            'Cuentas por pagar' => $summary['accounts_payable'] ?? 0,
            'Documentos' => $summary['documents'] ?? 0,
            'Mecanica' => $summary['mechanics'] ?? 0,
        ] as $label => $count)
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="deployment-summary-{{ $label }}">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $label }}</p>
                <p class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white">{{ $count }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Pasar a produccion</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Elimina datos operativos de prueba, usuarios demo, documentos, pagos, mecanica, almacen y secuencias generadas.
            </p>
            <button
                type="button"
                wire:click="openProductionConfirmation"
                class="mt-5 inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400"
            >
                Pasar a produccion
            </button>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Volver a desarrollo</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Limpia datos operativos actuales y vuelve a insertar los datos demo definidos en los seeders del sistema.
            </p>
            <button
                type="button"
                wire:click="openDevelopmentConfirmation"
                class="mt-5 inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
            >
                Volver a desarrollo
            </button>
        </div>
    </div>

    <x-platform.modal :show="$showConfirmationModal" max-width="max-w-xl">
        <div class="space-y-5">
            <div>
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">Confirmar cambio a {{ $this->targetModeLabel() }}</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                    Esta accion modifica datos del sistema. Escribe {{ $this->expectedConfirmation() }} para continuar.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Confirmacion</label>
                <input
                    wire:model="confirmation"
                    type="text"
                    class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm uppercase dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    autocomplete="off"
                />
                @error('confirmation') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <button
                    type="button"
                    wire:click="closeConfirmation"
                    class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    wire:click="confirmModeChange"
                    wire:loading.attr="disabled"
                    class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-60 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400"
                >
                    Confirmar
                </button>
            </div>
        </div>
    </x-platform.modal>
</div>
