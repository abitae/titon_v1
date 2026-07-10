<div class="flex flex-1 flex-col gap-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Seguridad</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-950 dark:text-white">{{ $title }}</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Catalogo de permisos con descripcion corta por accion y modulo.
                </p>
            </div>

            <div class="grid w-full max-w-xl gap-3 sm:grid-cols-2">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar permiso o descripcion"
                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
                <select
                    wire:model.live="moduleFilter"
                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                >
                    <option value="">Todos los modulos</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module }}">{{ str($module)->replace(['-', '_'], ' ')->title() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <x-platform.compact-table :headers="['Permiso', 'Descripcion', 'Modulo']">
        @forelse ($permissions as $permission)
            <tr wire:key="permission-{{ $permission->id }}" class="align-top">
                <td class="font-mono text-sm text-slate-950 dark:text-white">{{ $permission->name }}</td>
                <td class="text-slate-600 dark:text-slate-300">{{ $permission->description }}</td>
                <td class="text-slate-600 dark:text-slate-300">
                    {{ str(explode('.', $permission->name, 2)[0])->replace(['-', '_'], ' ')->title() }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="py-8 text-center text-sm text-slate-500">No hay permisos que coincidan con el filtro.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>
</div>
