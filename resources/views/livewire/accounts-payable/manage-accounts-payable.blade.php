<div class="space-y-4">
    <div>
        <h1 class="text-lg font-semibold text-slate-950 dark:text-white">Cuentas por pagar</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">CxP derivadas de órdenes con conformidad en obra.</p>
    </div>

    <div class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-white px-2 py-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="min-w-[10rem] flex-1">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Código o proveedor" />
        </div>
        <div class="w-44">
            <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</label>
            <select wire:model.live="statusFilter" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($statusOptions as $statusOption)
                    <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <x-platform.compact-table dense :headers="['CxP', 'Proveedor / Obra', 'Monto', 'Saldo', 'Estado', '']">
        @forelse ($accounts as $account)
            <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="accounts-payable-{{ $account->id }}">
                <td class="whitespace-nowrap px-2.5 py-1.5">
                    <p class="font-semibold leading-tight text-slate-950 dark:text-white">{{ $account->code }}</p>
                    <p class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400">{{ $account->order?->code ?? 'Sin OC' }}</p>
                </td>
                <td class="max-w-[14rem] px-2.5 py-1.5">
                    <p class="truncate font-medium leading-tight text-slate-950 dark:text-white">{{ $account->supplier?->business_name ?? 'Sin proveedor' }}</p>
                    <p class="mt-0.5 truncate text-[10px] text-slate-500 dark:text-slate-400">{{ $account->project?->name ?? 'Sin obra' }}</p>
                </td>
                <td class="whitespace-nowrap px-2.5 py-1.5 tabular-nums">
                    {{ number_format((float) $account->amount, 2) }} <span class="text-[10px] text-slate-500">{{ $account->currency }}</span>
                </td>
                <td class="whitespace-nowrap px-2.5 py-1.5 tabular-nums font-medium text-slate-950 dark:text-white">
                    {{ number_format((float) $account->balance, 2) }}
                </td>
                <td class="whitespace-nowrap px-2.5 py-1.5">
                    <x-platform.status-badge :value="$account->status" size="xs" />
                </td>
                <td class="whitespace-nowrap px-1.5 py-1">
                    <div class="flex items-center justify-end">
                        <flux:tooltip content="Detalle">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="eye"
                                href="{{ route('accounts-payable.show', $account) }}"
                                wire:navigate
                                class="!size-7 !min-h-0 !p-0 !text-cyan-700 hover:!text-cyan-800 dark:!text-cyan-300 dark:hover:!text-cyan-200"
                                aria-label="Ver detalle"
                            />
                        </flux:tooltip>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-2.5 py-6 text-center text-[11px] text-slate-500 dark:text-slate-400">No hay cuentas por pagar registradas.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-xl border border-slate-200 bg-white px-2 py-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $accounts->links() }}
    </div>
</div>
