<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ $accountsPayable->code }}</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $accountsPayable->supplier?->business_name }} · {{ $accountsPayable->project?->name }}</p>
    </div>

    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border p-4 dark:border-slate-800"><p class="text-sm text-slate-500">Monto</p><p class="text-xl font-semibold">{{ number_format((float) $accountsPayable->amount, 2) }}</p></div>
        <div class="rounded-2xl border p-4 dark:border-slate-800"><p class="text-sm text-slate-500">Pagado</p><p class="text-xl font-semibold">{{ number_format((float) $accountsPayable->paid_amount, 2) }}</p></div>
        <div class="rounded-2xl border p-4 dark:border-slate-800"><p class="text-sm text-slate-500">Saldo</p><p class="text-xl font-semibold">{{ number_format((float) $accountsPayable->balance, 2) }}</p></div>
    </section>

    <div class="rounded-3xl border p-6 dark:border-slate-800">
        <h2 class="font-semibold">Checklist de documentos</h2>
        <ul class="mt-4 space-y-2">
            @foreach ($accountsPayable->documents as $document)
                <li class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-950">
                    <span>{{ $document->document_type }} @if($document->required)<span class="text-red-500">*</span>@endif</span>
                    @if ($document->uploaded)
                        <span class="text-emerald-600 text-sm">Cargado</span>
                    @else
                        <button type="button" wire:click="markDocumentUploaded({{ $document->id }})" class="text-sm text-cyan-600">Marcar cargado</button>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    @canany(['cuentas_pagar.pagar', 'payments.crear'])
        <form wire:submit="registerPayment" class="rounded-3xl border p-6 dark:border-slate-800">
            <h2 class="font-semibold">Registrar pago</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div><label class="text-sm">Monto</label><input wire:model="payment_amount" type="number" step="0.01" class="mt-2 w-full rounded-xl border px-3 py-2 text-sm dark:bg-slate-950" /></div>
                <div><label class="text-sm">Fecha</label><input wire:model="payment_date" type="date" class="mt-2 w-full rounded-xl border px-3 py-2 text-sm dark:bg-slate-950" /></div>
                <div class="md:col-span-2"><label class="text-sm">Concepto</label><input wire:model="concept" class="mt-2 w-full rounded-xl border px-3 py-2 text-sm dark:bg-slate-950" /></div>
            </div>
            <button type="submit" class="mt-4 rounded-xl bg-slate-950 px-4 py-2 text-sm text-white dark:bg-cyan-500 dark:text-slate-950" @disabled(!$accountsPayable->requiredDocumentsUploaded())>Registrar pago</button>
        </form>
    @endcanany
</div>
