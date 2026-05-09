<x-layouts::app :title="'Empresas'">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Empresas</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Administra las empresas disponibles y su configuracion visual.</p>
            </div>

            @can('companies.crear')
                <a href="{{ route('companies.create') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400">
                    Nueva empresa
                </a>
            @endcan
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/60">
                        <tr class="text-left text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                            <th class="px-6 py-4">Empresa</th>
                            <th class="px-6 py-4">RUC</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4">Contacto</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach ($companies as $company)
                            <tr class="text-sm text-slate-700 dark:text-slate-200">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-950 dark:text-white">{{ $company->name }}</p>
                                    <p class="text-slate-500 dark:text-slate-400">{{ $company->business_name }}</p>
                                </td>
                                <td class="px-6 py-4">{{ $company->ruc }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $company->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                                        {{ $company->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p>{{ $company->email ?? 'Sin correo' }}</p>
                                    <p class="text-slate-500 dark:text-slate-400">{{ $company->phone ?? 'Sin telefono' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        @can('companies.editar')
                                            <a href="{{ route('companies.edit', $company) }}" class="text-sm font-medium text-cyan-700 hover:text-cyan-600 dark:text-cyan-300 dark:hover:text-cyan-200">
                                                Editar
                                            </a>
                                        @endcan
                                        @can('companies.eliminar')
                                            <form method="POST" action="{{ route('companies.destroy', $company) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-medium text-rose-700 hover:text-rose-600 dark:text-rose-300 dark:hover:text-rose-200">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                {{ $companies->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>
