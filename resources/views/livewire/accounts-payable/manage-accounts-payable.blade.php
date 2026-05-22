<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Cuentas por pagar</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Gestión de CxP derivadas de órdenes con conformidad en obra.</p>
        </div>
    </div>

    <x-platform.filter-bar>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-2 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Código o proveedor" />
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Estado</label>
            <select wire:model.live="statusFilter" class="mt-2 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                <option value="pendiente_documentos">Pendiente documentos</option>
                <option value="lista_para_pago">Lista para pago</option>
                <option value="pago_parcial">Pago parcial</option>
                <option value="pagada">Pagada</option>
            </select>
        </div>
    </x-platform.filter-bar>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500 dark:bg-slate-950">
                <tr>
                    <th class="px-6 py-3">Código</th>
                    <th class="px-6 py-3">Proveedor</th>
                    <th class="px-6 py-3">Obra</th>
                    <th class="px-6 py-3">Monto</th>
                    <th class="px-6 py-3">Saldo</th>
                    <th class="px-6 py-3">Estado</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($accounts as $account)
                    <tr class="border-t border-slate-100 dark:border-slate-800">
                        <td class="px-6 py-4 font-medium">{{ $account->code }}</td>
                        <td class="px-6 py-4">{{ $account->supplier?->business_name }}</td>
                        <td class="px-6 py-4">{{ $account->project?->name }}</td>
                        <td class="px-6 py-4">{{ number_format((float) $account->amount, 2) }} {{ $account->currency }}</td>
                        <td class="px-6 py-4">{{ number_format((float) $account->balance, 2) }}</td>
                        <td class="px-6 py-4"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs dark:bg-slate-800">{{ $account->status }}</span></td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('accounts-payable.show', $account) }}" class="text-cyan-600 hover:underline">Detalle</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-6 py-4 dark:border-slate-800">{{ $accounts->links() }}</div>
    </div>
</div>
