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

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/60">
                        <tr class="text-left text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                            <th class="px-6 py-4">Usuario</th>
                            <th class="px-6 py-4">Empresas</th>
                            <th class="px-6 py-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach ($users as $user)
                            <tr class="text-sm text-slate-700 dark:text-slate-200">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-950 dark:text-white">{{ $user->name }}</p>
                                    <p class="text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($user->companies as $company)
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                                {{ $company->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @can('users.editar')
                                            <a href="{{ route('users.edit', $user) }}" class="font-medium text-cyan-700 hover:text-cyan-600 dark:text-cyan-300 dark:hover:text-cyan-200">
                                                Editar
                                            </a>
                                        @endcan
                                        @can('users.eliminar')
                                            @if (auth()->id() !== $user->id)
                                                <form method="POST" action="{{ route('users.destroy', $user) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-medium text-rose-700 hover:text-rose-600 dark:text-rose-300 dark:hover:text-rose-200">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>
