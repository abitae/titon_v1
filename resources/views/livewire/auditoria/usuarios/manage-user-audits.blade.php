<div class="flex flex-1 flex-col gap-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Trazabilidad multiempresa</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-950 dark:text-white">{{ $title }}</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Visualiza acciones por usuario, empresa, modulo y registro afectado.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <flux:button wire:click="exportExcel" variant="filled" size="sm">Exportar Excel</flux:button>
                <flux:button wire:click="exportPdf" variant="primary" size="sm">Exportar PDF</flux:button>
            </div>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar usuario, modulo o accion" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
            <select wire:model.live="companyFilter" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas las empresas</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="userFilter" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos los usuarios</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="moduleFilter" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos los modulos</option>
                @foreach ($modules as $module)
                    <option value="{{ $module }}">{{ $module }}</option>
                @endforeach
            </select>
            <select wire:model.live="actionFilter" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas las acciones</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}">{{ str($action)->replace('_', ' ')->title() }}</option>
                @endforeach
            </select>
            <input wire:model.live="dateFrom" type="date" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
            <input wire:model.live="dateTo" type="date" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
        </div>
    </section>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-950/60">
                    <tr class="text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">Empresa</th>
                        <th class="px-4 py-3">Modulo</th>
                        <th class="px-4 py-3">Accion</th>
                        <th class="px-4 py-3">Registro</th>
                        <th class="px-4 py-3">Contexto</th>
                        <th class="px-4 py-3">Cambios</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($audits as $audit)
                        <tr class="align-top">
                            <td class="px-4 py-4 text-sm text-slate-600 dark:text-slate-300">
                                {{ $audit->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-4 text-sm">
                                <p class="font-medium text-slate-950 dark:text-white">{{ $audit->user_name ?: 'Sistema' }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $audit->active_role ?: 'Sin rol activo' }}</p>
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-600 dark:text-slate-300">
                                {{ $audit->company?->name ?? 'Sin empresa' }}
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-950 dark:text-white">
                                {{ $audit->module ?: 'Sistema' }}
                            </td>
                            <td class="px-4 py-4 text-sm">
                                <x-platform.status-badge :value="$audit->action ?: $audit->event" />
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-600 dark:text-slate-300">
                                <p>{{ class_basename((string) $audit->auditable_type) ?: 'N/A' }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">ID {{ $audit->auditable_id ?: '-' }}</p>
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-600 dark:text-slate-300">
                                <p>{{ $audit->ip_address ?: 'Sin IP' }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $audit->browser ?: 'Sin navegador' }} - {{ $audit->device ?: 'Sin dispositivo' }}</p>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-600 dark:text-slate-300">
                                <details class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-950/60">
                                    <summary class="cursor-pointer font-medium text-slate-800 dark:text-slate-200">Ver cambios</summary>
                                    <div class="mt-3 grid gap-3 lg:grid-cols-2">
                                        <div>
                                            <p class="mb-1 font-semibold text-slate-700 dark:text-slate-300">Antes</p>
                                            <pre class="overflow-x-auto whitespace-pre-wrap rounded-lg bg-white p-2 text-[11px] dark:bg-slate-900">{{ json_encode($audit->old_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                        <div>
                                            <p class="mb-1 font-semibold text-slate-700 dark:text-slate-300">Despues</p>
                                            <pre class="overflow-x-auto whitespace-pre-wrap rounded-lg bg-white p-2 text-[11px] dark:bg-slate-900">{{ json_encode($audit->new_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    </div>
                                    @if ($audit->observation)
                                        <p class="mt-3 text-[11px] text-slate-500 dark:text-slate-400">{{ $audit->observation }}</p>
                                    @endif
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                No hay registros de auditoria para los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
            {{ $audits->links() }}
        </div>
    </section>
</div>
