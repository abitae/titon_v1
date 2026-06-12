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
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar usuario, modulo o accion" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
            <select wire:model.live="companyFilter" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas las empresas</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="userFilter" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos los usuarios</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="moduleFilter" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos los modulos</option>
                @foreach ($modules as $module)
                    <option value="{{ $module }}">{{ $module }}</option>
                @endforeach
            </select>
            <select wire:model.live="actionFilter" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas las acciones</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}">{{ str($action)->replace('_', ' ')->title() }}</option>
                @endforeach
            </select>
            <input wire:model.live="dateFrom" type="date" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
            <input wire:model.live="dateTo" type="date" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
        </div>
    </section>

    <x-platform.compact-table :headers="['Fecha', 'Usuario', 'Empresa', 'Módulo', 'Acción', 'Registro', 'Contexto', 'Cambios']">
        @forelse ($audits as $audit)
            <tr wire:key="audit-{{ $audit->id }}" class="align-top">
                <td class="whitespace-nowrap text-slate-600 dark:text-slate-300">
                    {{ $audit->created_at?->format('d/m/Y H:i') }}
                </td>
                <td>
                    <p class="font-medium text-slate-950 dark:text-white">{{ $audit->user_name ?: 'Sistema' }}</p>
                    <p class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400">{{ $audit->active_role ?: 'Sin rol activo' }}</p>
                </td>
                <td class="text-slate-600 dark:text-slate-300">
                    {{ $audit->company?->name ?? 'Sin empresa' }}
                </td>
                <td class="font-medium text-slate-950 dark:text-white">
                    {{ $audit->module ?: 'Sistema' }}
                </td>
                <td>
                    <x-platform.status-badge :value="$audit->action ?: $audit->event" size="xs" />
                </td>
                <td class="text-slate-600 dark:text-slate-300">
                    <p>{{ class_basename((string) $audit->auditable_type) ?: 'N/A' }}</p>
                    <p class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400">ID {{ $audit->auditable_id ?: '-' }}</p>
                </td>
                <td class="text-slate-600 dark:text-slate-300">
                    <p>{{ $audit->ip_address ?: 'Sin IP' }}</p>
                    <p class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400">{{ $audit->browser ?: 'Sin navegador' }} · {{ $audit->device ?: 'Sin dispositivo' }}</p>
                </td>
                <td>
                    <details class="rounded-lg border border-slate-200 bg-slate-50 p-2 dark:border-slate-700 dark:bg-slate-950/60">
                        <summary class="cursor-pointer text-[11px] font-medium text-slate-800 dark:text-slate-200">Ver cambios</summary>
                        <div class="mt-2 grid gap-2 lg:grid-cols-2">
                            <div>
                                <p class="mb-1 text-[10px] font-semibold uppercase text-slate-500">Antes</p>
                                <pre class="overflow-x-auto whitespace-pre-wrap rounded bg-white p-1.5 text-[10px] dark:bg-slate-900">{{ json_encode($audit->old_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            <div>
                                <p class="mb-1 text-[10px] font-semibold uppercase text-slate-500">Después</p>
                                <pre class="overflow-x-auto whitespace-pre-wrap rounded bg-white p-1.5 text-[10px] dark:bg-slate-900">{{ json_encode($audit->new_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                        @if ($audit->observation)
                            <p class="mt-2 text-[10px] text-slate-500 dark:text-slate-400">{{ $audit->observation }}</p>
                        @endif
                    </details>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="!py-5 text-center text-[11px] text-slate-500 dark:text-slate-400">
                    No hay registros de auditoría para los filtros seleccionados.
                </td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $audits->links() }}
    </div>
</div>
