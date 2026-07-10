<x-layouts::app :title="'Usuarios'">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Usuarios</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Gestiona accesos, empresas asignadas y rol por empresa.</p>
            </div>

            @can('users.crear')
                <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400">
                    Nuevo usuario
                </a>
            @endcan
        </div>

        <x-platform.compact-table :headers="['Usuario', 'Empresas', 'Acciones.']">
            @foreach ($users as $user)
                <tr class="text-xs text-slate-700 dark:text-slate-200">
                    <td>
                        <p class="font-medium text-slate-950 dark:text-white">{{ $user->name }}</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                    </td>
                    <td>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($user->companies as $company)
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                    {{ $company->name }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="!px-1.5 !py-1">
                        <x-platform.action-buttons
                            :edit-href="auth()->user()->can('update', $user) ? route('users.edit', $user) : null"
                            :delete-url="(auth()->id() !== $user->id && auth()->user()->can('delete', $user)) ? route('users.destroy', $user) : null"
                            delete-confirm="¿Eliminar este usuario?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-platform.compact-table>

        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            {{ $users->links() }}
        </div>
    </div>
</x-layouts::app>
