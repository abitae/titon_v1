<x-layouts::app :title="$module->label()">
    <div class="flex flex-1 flex-col gap-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-medium uppercase tracking-[0.24em] text-cyan-700 dark:text-cyan-300">Modulo</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">
                        {{ $module->label() }}
                    </h1>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        {{ $module->description() }}
                    </p>
                </div>

                <flux:badge color="zinc" size="sm">Placeholder</flux:badge>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-[1.3fr_1fr]">
            <article class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-6 dark:border-slate-700 dark:bg-slate-950/60">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Espacio reservado para el modulo</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Esta pantalla ya queda protegida por autenticacion y verificacion, enlazada en la navegacion lateral y lista para recibir componentes Livewire, acciones y politicas propias del dominio.
                </p>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-sm font-medium uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Checklist</h2>
                <div class="mt-4 space-y-3">
                    @foreach ([
                        'Modelo y migraciones del dominio',
                        'Acciones de aplicacion',
                        'Politicas y permisos',
                        'Reportes y exportaciones',
                    ] as $task)
                        <div class="rounded-2xl border border-slate-200 px-2.5 py-1.5 text-sm text-slate-700 dark:border-slate-800 dark:text-slate-200">
                            {{ $task }}
                        </div>
                    @endforeach
                </div>
            </article>
        </section>
    </div>
</x-layouts::app>
