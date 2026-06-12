<div class="space-y-6">
    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Total pagado</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">S/ {{ number_format($summary['total_paid'], 2) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Contratos con saldo</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($summary['contracts_with_balance']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Pagos registrados</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($summary['payments_count']) }}</p>
        </div>
    </section>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Pagos a proveedores</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Registra pagos a cuenta, adjunta vouchers y controla el estado de cuenta por contrato y proveedor.</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Registrar pago</button>
    </div>

    <x-platform.filter-bar>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Operacion o concepto" />
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Obra</label>
            <select wire:model.live="projectFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Proveedor</label>
            <select wire:model.live="supplierFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->business_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Contrato</label>
            <select wire:model.live="contractFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($contracts as $contract)
                    <option value="{{ $contract->id }}">{{ $contract->contract_number }} · {{ $contract->supplier?->business_name }}</option>
                @endforeach
            </select>
        </div>
    </x-platform.filter-bar>

    @if ($supplierSummaries->isNotEmpty())
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Estado de cuenta por proveedor</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @foreach ($supplierSummaries as $supplierSummary)
                    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                        <p class="font-medium text-slate-950 dark:text-white">{{ $supplierSummary['contract']->contract_number }}</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $supplierSummary['contract']->project?->name ?? 'Sin obra' }}</p>
                        <div class="mt-3 flex flex-wrap gap-3 text-sm">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700 dark:bg-slate-800 dark:text-slate-200">Pagado: {{ $supplierSummary['contract']->currency }} {{ number_format($supplierSummary['total_paid'], 2) }}</span>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">Saldo: {{ $supplierSummary['contract']->currency }} {{ number_format($supplierSummary['pending_balance'], 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <x-platform.compact-table :headers="['Fecha', 'Proveedor', 'Contrato', 'Cuota', 'Monto', 'Concepto', 'Acciones']">
        @forelse ($payments as $payment)
            <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="payment-row-{{ $payment->id }}">
                <td class="px-2.5 py-1.5">{{ $payment->payment_date?->format('d/m/Y') }}</td>
                <td class="px-2.5 py-1.5">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $payment->supplier?->business_name ?? 'Sin proveedor' }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ $payment->project?->name ?? 'Sin obra' }}</p>
                </td>
                <td class="px-2.5 py-1.5">
                    <p>{{ $payment->supplierContract?->contract_number ?? 'Sin contrato' }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ $payment->operationType?->name ?? 'Sin tipo' }}</p>
                </td>
                <td class="px-2.5 py-1.5">
                    @if ($payment->schedule)
                        <div>
                            <p>Cuota {{ $payment->schedule->installment_number }}</p>
                            <div class="mt-1"><x-platform.status-badge :value="$payment->schedule->status" /></div>
                        </div>
                    @else
                        <span class="text-slate-500 dark:text-slate-400">Sin cuota</span>
                    @endif
                </td>
                <td class="px-2.5 py-1.5">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                <td class="px-2.5 py-1.5">
                    <p>{{ $payment->concept }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ $payment->operation_number ?: 'Sin operacion' }}</p>
                </td>
                <td class="px-2.5 py-1.5">
                    <div class="flex flex-wrap gap-2 justify-end">
                        @if ($payment->supplierContract)
                            <a href="{{ route('payments.schedules', $payment->supplierContract) }}" class="rounded-lg px-2 py-1 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200">Cronograma</a>
                        @endif
                        @if ($payment->getFirstMediaUrl('voucher'))
                            <a href="{{ $payment->getFirstMediaUrl('voucher') }}" target="_blank" class="rounded-lg px-2 py-1 text-sm font-medium text-cyan-700 hover:bg-cyan-50 dark:text-cyan-300">Voucher</a>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No hay pagos registrados para los filtros seleccionados.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-3xl border border-slate-200 bg-white px-2.5 py-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $payments->links() }}
    </div>

    <x-platform.modal :show="$showFormModal" max-width="max-w-5xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">Registrar pago</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Asocia el pago al contrato, cuota y comprobante correspondiente.</p>
            </div>
            <button type="button" wire:click="closeModal" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Contrato</label>
                <select wire:model.live="supplier_contract_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($contracts as $contract)
                        <option value="{{ $contract->id }}">{{ $contract->contract_number }} · {{ $contract->supplier?->business_name }}</option>
                    @endforeach
                </select>
                @error('supplier_contract_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Cuota</label>
                <select wire:model="contract_payment_schedule_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Sin cuota especifica</option>
                    @foreach ($schedules as $schedule)
                        <option value="{{ $schedule->id }}">Cuota {{ $schedule->installment_number }} · saldo {{ number_format((float) $schedule->balance, 2) }}</option>
                    @endforeach
                </select>
                @error('contract_payment_schedule_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Obra</label>
                <select wire:model="work_project_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
                @error('work_project_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Proveedor</label>
                <select wire:model="supplier_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->business_name }}</option>
                    @endforeach
                </select>
                @error('supplier_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Fecha de pago</label>
                <input wire:model="payment_date" type="date" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('payment_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Monto</label>
                <input wire:model="amount" type="number" min="0.01" step="0.01" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('amount') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Moneda</label>
                <input wire:model="currency" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Responsable</label>
                <select wire:model="responsible_user_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @foreach ($companyUsers as $companyUser)
                        <option value="{{ $companyUser->id }}">{{ $companyUser->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Tipo de operacion</label>
                <select wire:model="operation_type_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($operationTypes as $operationType)
                        <option value="{{ $operationType->id }}">{{ $operationType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Metodo de pago</label>
                <select wire:model="payment_method_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($paymentMethods as $paymentMethod)
                        <option value="{{ $paymentMethod->id }}">{{ $paymentMethod->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Cuenta de origen</label>
                <select wire:model="bank_account_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Seleccionar</option>
                    @foreach ($bankAccounts as $bankAccount)
                        <option value="{{ $bankAccount->id }}">{{ $bankAccount->displayLabel() }} ({{ number_format((float) $bankAccount->balance, 2) }})</option>
                    @endforeach
                </select>
                @error('bank_account_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Numero de operacion</label>
                <input wire:model="operation_number" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Concepto</label>
                <input wire:model="concept" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('concept') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Observacion</label>
                <textarea wire:model="observation" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Voucher</label>
                <input wire:model="voucher" type="file" multiple class="mt-2 block w-full rounded-xl border border-dashed border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('voucher.*') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" wire:click="closeModal" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancelar</button>
            <button type="button" wire:click="savePayment" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Guardar pago</button>
        </div>
    </x-platform.modal>
</div>
