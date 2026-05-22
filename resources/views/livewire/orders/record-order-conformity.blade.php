<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">Conformidad · Orden {{ $order->code }}</h1>
        <p class="text-sm text-slate-600">{{ $order->project?->name }} · {{ $order->supplier?->business_name }}</p>
    </div>
    <form wire:submit="save" class="rounded-3xl border p-6 dark:border-slate-800">
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium">Resultado</label>
                <select wire:model="result" class="mt-2 w-full rounded-xl border px-3 py-2 text-sm dark:bg-slate-950">
                    <option value="conforme">Conforme</option>
                    <option value="rechazado">Rechazado</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Fecha</label>
                <input type="date" wire:model="conformity_date" class="mt-2 w-full rounded-xl border px-3 py-2 text-sm dark:bg-slate-950" />
            </div>
            <div>
                <label class="text-sm font-medium">Observación</label>
                <textarea wire:model="observation" rows="4" class="mt-2 w-full rounded-xl border px-3 py-2 text-sm dark:bg-slate-950"></textarea>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('purchases.orders') }}" class="rounded-xl border px-4 py-2 text-sm">Cancelar</a>
            <button type="submit" class="rounded-xl bg-slate-950 px-4 py-2 text-sm text-white dark:bg-cyan-500 dark:text-slate-950">Guardar</button>
        </div>
    </form>
</div>
