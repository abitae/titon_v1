<div class="space-y-4">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-950 dark:text-white">Bancos y caja</h1>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Gestione cuentas, saldos y movimientos de entrada y salida.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('bancos.crear')
                <flux:button type="button" wire:click="openMovementModal(null, 'deposit')" variant="outline" size="sm">Registrar depósito</flux:button>
                <flux:button type="button" wire:click="openMovementModal(null, 'withdrawal')" variant="outline" size="sm">Registrar retiro</flux:button>
                <flux:button type="button" wire:click="openCreateAccountModal" variant="primary" size="sm">Nueva cuenta</flux:button>
            @endcan
        </div>
    </div>

    <div class="grid gap-2 sm:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white px-2 py-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Saldo total activo</p>
            <p class="mt-0.5 text-base font-semibold tabular-nums text-emerald-700 dark:text-emerald-300">{{ number_format($totalBalance, 2) }} PEN</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-2 py-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Cuentas activas</p>
            <p class="mt-0.5 text-base font-semibold text-slate-950 dark:text-white">{{ $accounts->total() }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Cuentas</h2>
            <input wire:model.live.debounce.300ms="accountSearch" class="h-8 w-full max-w-xs rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Buscar cuenta…" />
        </div>

        <x-platform.compact-table dense :headers="['Cuenta', 'Tipo', 'Saldo', 'Estado', '']" class="mt-2">
            @forelse ($accounts as $account)
                <tr wire:key="bank-account-{{ $account->id }}" class="text-xs text-slate-700 dark:text-slate-200">
                    <td class="px-2.5 py-1.5">
                        <p class="font-medium text-slate-950 dark:text-white">{{ $account->displayLabel() }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $account->currency }}</p>
                    </td>
                    <td class="whitespace-nowrap px-2.5 py-1.5">{{ $account->is_cash ? 'Caja' : 'Banco' }}</td>
                    <td class="whitespace-nowrap px-2.5 py-1.5 tabular-nums font-medium">{{ number_format((float) $account->balance, 2) }}</td>
                    <td class="whitespace-nowrap px-2.5 py-1.5">
                        <x-platform.status-badge :value="$account->is_active ? 'active' : 'inactive'" size="xs" />
                    </td>
                    <td class="whitespace-nowrap px-1.5 py-1 text-end">
                        @can('bancos.editar')
                            <x-platform.action-buttons :edit="'openEditAccountModal('.$account->id.')'" />
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-2.5 py-4 text-center text-[11px] text-slate-500 dark:text-slate-400">No hay cuentas registradas.</td>
                </tr>
            @endforelse
        </x-platform.compact-table>

        <div class="mt-2">{{ $accounts->links() }}</div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-2 md:grid-cols-3">
            <div class="md:col-span-2">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Movimientos</h2>
            </div>
            <input wire:model.live.debounce.300ms="movementSearch" class="h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Buscar movimiento…" />
            <select wire:model.live="accountFilter" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas las cuentas</option>
                @foreach ($accounts as $accountOption)
                    <option value="{{ $accountOption->id }}">{{ $accountOption->displayLabel() }}</option>
                @endforeach
            </select>
            <select wire:model.live="movementTypeFilter" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="all">Todos los tipos</option>
                @foreach ($movementTypes as $movementType)
                    <option value="{{ $movementType->value() }}">{{ $movementType->label() }}</option>
                @endforeach
            </select>
        </div>

        <x-platform.compact-table dense :headers="['Código', 'Fecha', 'Cuenta', 'Tipo', 'Monto', 'Saldo', 'Concepto']" class="mt-2">
            @forelse ($movements as $movement)
                <tr wire:key="bank-movement-{{ $movement->id }}" class="text-xs text-slate-700 dark:text-slate-200">
                    <td class="whitespace-nowrap px-2.5 py-1.5 font-medium text-slate-950 dark:text-white">{{ $movement->movement_code }}</td>
                    <td class="whitespace-nowrap px-2.5 py-1.5">{{ $movement->movement_date->format('d/m/Y') }}</td>
                    <td class="px-2.5 py-1.5">{{ $movement->bankAccount?->displayLabel() }}</td>
                    <td class="whitespace-nowrap px-2.5 py-1.5">
                        <span @class([
                            'font-medium',
                            'text-emerald-700 dark:text-emerald-300' => $movement->direction === 'inbound',
                            'text-rose-700 dark:text-rose-300' => $movement->direction === 'outbound',
                        ])>
                            {{ $movement->typeLabel() }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-2.5 py-1.5 tabular-nums">
                        {{ $movement->direction === 'inbound' ? '+' : '-' }}{{ number_format((float) $movement->amount, 2) }}
                    </td>
                    <td class="whitespace-nowrap px-2.5 py-1.5 tabular-nums">{{ number_format((float) $movement->balance_after, 2) }}</td>
                    <td class="px-2.5 py-1.5">
                        <p>{{ $movement->concept }}</p>
                        @if ($movement->reference)
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $movement->reference }}</p>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-2.5 py-4 text-center text-[11px] text-slate-500 dark:text-slate-400">Sin movimientos registrados.</td>
                </tr>
            @endforelse
        </x-platform.compact-table>

        <div class="mt-2">{{ $movements->links() }}</div>
    </div>

    <x-platform.modal :show="$showAccountModal" max-width="max-w-2xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $editingAccountId ? 'Editar cuenta' : 'Nueva cuenta' }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Registre una cuenta bancaria o caja de efectivo.</p>
            </div>
            <flux:button type="button" wire:click="closeAccountModal" variant="ghost" size="sm">Cerrar</flux:button>
        </div>

        <form wire:submit="saveAccount" class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nombre</label>
                <input wire:model="account_name" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('account_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2 flex items-center gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <input wire:model.live="is_cash" type="checkbox" class="rounded border-slate-300 dark:border-slate-600" />
                    Es caja (efectivo)
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <input wire:model="is_active" type="checkbox" class="rounded border-slate-300 dark:border-slate-600" />
                    Activa
                </label>
            </div>
            @unless ($is_cash)
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Institución</label>
                    <select wire:model="catalog_bank_id" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">Seleccionar…</option>
                        @foreach ($institutions as $institution)
                            <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                        @endforeach
                    </select>
                    @error('catalog_bank_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Número de cuenta</label>
                    <input wire:model="account_number" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('account_number') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
            @endunless
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Moneda</label>
                <input wire:model="currency" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('currency') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            @unless ($editingAccountId)
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Saldo inicial</label>
                    <input wire:model="opening_balance" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('opening_balance') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
            @endunless
            <div class="sm:col-span-2 flex justify-end">
                <flux:button type="submit" variant="primary" size="sm">Guardar cuenta</flux:button>
            </div>
        </form>
    </x-platform.modal>

    <x-platform.modal :show="$showMovementModal" max-width="max-w-xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $movement_kind === 'deposit' ? 'Registrar depósito' : 'Registrar retiro' }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Movimiento manual de entrada o salida de dinero.</p>
            </div>
            <flux:button type="button" wire:click="closeMovementModal" variant="ghost" size="sm">Cerrar</flux:button>
        </div>

        <form wire:submit="saveMovement" class="mt-4 grid gap-3">
            <input type="hidden" wire:model="movement_kind" />
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Cuenta</label>
                <select wire:model="movement_bank_account_id" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar…</option>
                    @foreach ($accounts as $accountOption)
                        <option value="{{ $accountOption->id }}">{{ $accountOption->displayLabel() }} ({{ number_format((float) $accountOption->balance, 2) }})</option>
                    @endforeach
                </select>
                @error('movement_bank_account_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Monto</label>
                    <input wire:model="movement_amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('movement_amount') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Fecha</label>
                    <input wire:model="movement_date" type="date" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('movement_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Concepto</label>
                <input wire:model="movement_concept" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('movement_concept') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Referencia</label>
                <input wire:model="movement_reference" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('movement_reference') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" size="sm">Registrar movimiento</flux:button>
            </div>
        </form>
    </x-platform.modal>
</div>
