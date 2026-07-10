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

        <x-platform.compact-table :headers="['Empresa', 'RUC', 'Estado', 'Contacto', 'Acciones.']">
            @foreach ($companies as $company)
                <tr class="text-xs text-slate-700 dark:text-slate-200">
                    <td>
                        <p class="font-medium text-slate-950 dark:text-white">{{ $company->name }}</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $company->business_name }}</p>
                    </td>
                    <td>{{ $company->ruc }}</td>
                    <td>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-medium {{ $company->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                            {{ $company->status }}
                        </span>
                    </td>
                    <td>
                        <p>{{ $company->email ?? 'Sin correo' }}</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $company->phone ?? 'Sin telefono' }}</p>
                    </td>
                    <td class="!px-1.5 !py-1">
                        <x-platform.action-buttons
                            :edit-href="auth()->user()->can('update', $company) ? route('companies.edit', $company) : null"
                            :delete-url="auth()->user()->can('delete', $company) ? route('companies.destroy', $company) : null"
                            delete-confirm="¿Eliminar esta empresa?"
                        />
                    </td>
                </tr>
            @endforeach
        </x-platform.compact-table>

        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            {{ $companies->links() }}
        </div>
    </div>
</x-layouts::app>
