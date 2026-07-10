<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $accountsPayable->code }}</h1>
                <x-platform.status-badge :value="$accountsPayable->status" size="xs" />
            </div>
            <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">
                {{ $accountsPayable->supplier?->business_name }}
                · {{ $accountsPayable->project?->name }}
                · OC {{ $accountsPayable->order?->code ?? '—' }}
            </p>
        </div>
        <flux:button variant="outline" href="{{ route('accounts-payable.index') }}" wire:navigate size="sm">Volver</flux:button>
    </div>

    <div class="grid gap-2 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white px-2 py-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Monto</p>
            <p class="mt-0.5 text-base font-semibold tabular-nums text-slate-950 dark:text-white">{{ number_format((float) $accountsPayable->amount, 2) }} {{ $accountsPayable->currency }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-2 py-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pagado</p>
            <p class="mt-0.5 text-base font-semibold tabular-nums text-slate-950 dark:text-white">{{ number_format((float) $accountsPayable->paid_amount, 2) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-2 py-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Saldo</p>
            <p class="mt-0.5 text-base font-semibold tabular-nums text-emerald-700 dark:text-emerald-300">{{ number_format((float) $accountsPayable->balance, 2) }}</p>
        </div>
    </div>

    @canany(['cuentas_pagar.subir_documentos', 'payments.crear'])
        <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Checklist de documentos</h2>
            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Suba los archivos requeridos para habilitar el pago.</p>

            <x-platform.compact-table dense :headers="['Documento', 'Requerido', 'Archivo', '']" class="mt-2">
                @forelse ($accountsPayable->documents as $document)
                    @php
                        $uploadedMedia = $document->getFirstMedia('archivo');
                    @endphp
                    <tr wire:key="payable-doc-{{ $document->id }}" class="text-xs text-slate-700 dark:text-slate-200">
                        <td class="px-2.5 py-1.5 font-medium text-slate-950 dark:text-white">{{ $document->typeLabel() }}</td>
                        <td class="whitespace-nowrap px-2.5 py-1.5">
                            @if ($document->required)
                                <span class="text-rose-600 dark:text-rose-400">Sí</span>
                            @else
                                <span class="text-slate-400">No</span>
                            @endif
                        </td>
                        <td class="px-2.5 py-1.5">
                            @if ($document->hasUploadedFile() && $uploadedMedia)
                                <button
                                    type="button"
                                    wire:click="openPayableDocumentPreview({{ $document->id }})"
                                    class="inline-flex max-w-[12rem] items-center gap-1 truncate text-[11px] font-medium text-cyan-700 hover:text-cyan-800 dark:text-cyan-300 dark:hover:text-cyan-200"
                                >
                                    <flux:icon.document variant="mini" class="size-3.5 shrink-0" />
                                    <span class="truncate">{{ $uploadedMedia->file_name }}</span>
                                </button>
                            @else
                                <input
                                    type="file"
                                    wire:model="document_files.{{ $document->id }}"
                                    accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*"
                                    class="block w-full max-w-[14rem] text-[11px] text-slate-600 file:me-2 file:rounded-md file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-[11px] file:font-medium file:text-slate-700 dark:text-slate-300 dark:file:bg-slate-800 dark:file:text-slate-200"
                                />
                                @error('document_files.'.$document->id)
                                    <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p>
                                @enderror
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-1.5 py-1 text-end">
                            @if ($document->hasUploadedFile())
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">Cargado</span>
                            @else
                                <flux:button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    wire:click="uploadDocument({{ $document->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="uploadDocument,document_files.{{ $document->id }}"
                                    class="!h-7 !px-2 !text-[11px] !text-cyan-700 dark:!text-cyan-300"
                                >
                                    <span wire:loading.remove wire:target="uploadDocument,document_files.{{ $document->id }}">Subir</span>
                                    <span wire:loading wire:target="uploadDocument,document_files.{{ $document->id }}">Subiendo…</span>
                                </flux:button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-2.5 py-4 text-center text-[11px] text-slate-500 dark:text-slate-400">Sin documentos configurados.</td>
                    </tr>
                @endforelse
            </x-platform.compact-table>
        </div>
    @else
        <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Checklist de documentos</h2>
            <x-platform.compact-table dense :headers="['Documento', 'Requerido', 'Estado']" class="mt-2">
                @forelse ($accountsPayable->documents as $document)
                    <tr wire:key="payable-doc-readonly-{{ $document->id }}" class="text-xs text-slate-700 dark:text-slate-200">
                        <td class="px-2.5 py-1.5 font-medium text-slate-950 dark:text-white">{{ $document->typeLabel() }}</td>
                        <td class="whitespace-nowrap px-2.5 py-1.5">{{ $document->required ? 'Sí' : 'No' }}</td>
                        <td class="whitespace-nowrap px-2.5 py-1.5">
                            @if ($document->hasUploadedFile())
                                <span class="text-emerald-600 dark:text-emerald-400">Cargado</span>
                            @else
                                <span class="text-slate-400">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-2.5 py-4 text-center text-[11px] text-slate-500 dark:text-slate-400">Sin documentos configurados.</td>
                    </tr>
                @endforelse
            </x-platform.compact-table>
        </div>
    @endcanany

    @if ($accountsPayable->payments->isNotEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Pagos registrados</h2>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Cada pago descuenta saldo de la cuenta configurada en Bancos.</p>
                </div>
                @canany(['cuentas_pagar.pagar', 'payments.crear'])
                    <flux:button
                        type="button"
                        variant="primary"
                        size="sm"
                        wire:click="openPaymentModal"
                        :disabled="! $this->canRegisterPayment()"
                    >
                        Registrar pago
                    </flux:button>
                @endcanany
            </div>

            <x-platform.compact-table dense :headers="['Fecha', 'Monto', 'Cuenta origen', 'Método', 'Operación', 'Concepto']" class="mt-2">
                @foreach ($accountsPayable->payments as $payment)
                    <tr wire:key="payable-payment-{{ $payment->id }}" class="text-xs text-slate-700 dark:text-slate-200">
                        <td class="whitespace-nowrap px-2.5 py-1.5">{{ $payment->payment_date->format('d/m/Y') }}</td>
                        <td class="whitespace-nowrap px-2.5 py-1.5 tabular-nums font-medium text-slate-950 dark:text-white">
                            {{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}
                        </td>
                        <td class="px-2.5 py-1.5">
                            <p class="font-medium text-slate-950 dark:text-white">{{ $payment->originAccountLabel() }}</p>
                            @if ($payment->bankMovement)
                                <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $payment->bankMovement->movement_code }}</p>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-2.5 py-1.5">{{ $payment->paymentMethod?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-2.5 py-1.5">
                            @if ($payment->operation_number)
                                <p>{{ $payment->operationType?->name ?? '—' }}</p>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $payment->operation_number }}</p>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-2.5 py-1.5">{{ $payment->concept }}</td>
                    </tr>
                @endforeach
            </x-platform.compact-table>
        </div>
    @endif

        </div>
    @elseif (auth()->user()->can('cuentas_pagar.pagar') || auth()->user()->can('payments.crear'))
        <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Pagos</h2>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Aún no hay pagos registrados para esta cuenta.</p>
                </div>
                <flux:button
                    type="button"
                    variant="primary"
                    size="sm"
                    wire:click="openPaymentModal"
                    :disabled="! $this->canRegisterPayment()"
                >
                    Registrar pago
                </flux:button>
            </div>
        </div>
    @endif

    @canany(['cuentas_pagar.pagar', 'payments.crear'])
        <x-platform.modal compact :show="$showPaymentModal" max-width="max-w-2xl">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-slate-950 dark:text-white">Registrar pago</h2>
                    <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                        Saldo pendiente: <strong class="text-slate-700 dark:text-slate-200">{{ number_format((float) $accountsPayable->balance, 2) }} {{ $accountsPayable->currency }}</strong>
                        · Cuenta en <strong>Operación → Bancos</strong> ({{ $accountsPayable->currency }})
                    </p>
                </div>
                <flux:button variant="ghost" size="sm" wire:click="closePaymentModal" type="button">Cerrar</flux:button>
            </div>

            @if ($configuredCashAccounts === 0 && $configuredBankAccounts === 0)
                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-2 py-1.5 text-[11px] text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">
                    No hay cuentas activas en {{ $accountsPayable->currency }}. Configure al menos una caja o cuenta bancaria antes de pagar.
                </div>
            @endif

            <form wire:submit="registerPayment" class="mt-3 grid gap-2 sm:grid-cols-2">
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Monto</label>
                    <input wire:model="payment_amount" type="number" step="0.01" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('payment_amount') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Método de pago</label>
                    <select wire:model.live="payment_method_id" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">Seleccione…</option>
                        @foreach ($paymentMethods as $paymentMethod)
                            <option value="{{ $paymentMethod->id }}">{{ $paymentMethod->name }}</option>
                        @endforeach
                    </select>
                    @error('payment_method_id') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>

                @if ($payment_method_id)
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $this->paymentMethodRequiresBankingDetails() ? 'Cuenta bancaria' : 'Caja' }}</label>
                        <select wire:model="bank_account_id" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="">Seleccione…</option>
                            @forelse ($bankAccounts as $bankAccount)
                                <option value="{{ $bankAccount->id }}">{{ $bankAccount->displayLabel() }} · Saldo {{ number_format((float) $bankAccount->balance, 2) }} {{ $bankAccount->currency }}</option>
                            @empty
                                <option value="" disabled>Sin cuentas disponibles en {{ $accountsPayable->currency }}</option>
                            @endforelse
                        </select>
                        @error('bank_account_id') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                        @if ($this->paymentMethodRequiresBankingDetails() && $configuredBankAccounts === 0)
                            <p class="mt-0.5 text-[11px] text-amber-700 dark:text-amber-300">Registre una cuenta bancaria en Bancos para pagos con transferencia, depósito o cheque.</p>
                        @elseif (! $this->paymentMethodRequiresBankingDetails() && $configuredCashAccounts === 0)
                            <p class="mt-0.5 text-[11px] text-amber-700 dark:text-amber-300">Registre una caja en Bancos para pagos en efectivo.</p>
                        @endif
                    </div>
                @endif

                @if ($this->paymentMethodRequiresBankingDetails())
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tipo de operación</label>
                        <select wire:model="operation_type_id" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="">Seleccione…</option>
                            @foreach ($operationTypes as $operationType)
                                <option value="{{ $operationType->id }}">{{ $operationType->name }}</option>
                            @endforeach
                        </select>
                        @error('operation_type_id') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">N.º de operación</label>
                        <input wire:model="operation_number" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Ej. 000123456" />
                        @error('operation_number') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fecha de pago</label>
                    <input wire:model="payment_date" type="date" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('payment_date') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Concepto</label>
                    <input wire:model="concept" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('concept') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2 flex flex-wrap items-center justify-between gap-2 pt-1">
                    @can('bancos.ver')
                        <flux:button variant="outline" href="{{ route('modules.banks') }}" wire:navigate size="sm">Gestionar cuentas</flux:button>
                    @else
                        <span></span>
                    @endcan
                    <div class="flex items-center gap-2">
                        <flux:button type="button" variant="outline" size="sm" wire:click="closePaymentModal">Cancelar</flux:button>
                        <flux:button type="submit" variant="primary" size="sm" :disabled="! $this->canRegisterPayment()">
                            Registrar pago
                        </flux:button>
                    </div>
                </div>
            </form>
        </x-platform.modal>
    @endcanany

    <x-platform.pdf-viewer-modal
        :show="$showPdfModal"
        :url="$pdfViewerUrl"
        :title="$pdfViewerTitle"
        :subtitle="$pdfViewerSubtitle"
        :allow-external-open="false"
    />
</div>
