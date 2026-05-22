<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Enviar requerimiento {{ $purchaseRequest->code }}</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Obra: {{ $purchaseRequest->project?->name }}</p>
    </div>

    <form wire:submit="send" class="rounded-3xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="text-sm font-medium">Proveedores</label>
                <select wire:model="supplier_ids" multiple class="mt-2 block min-h-32 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->business_name }}</option>
                    @endforeach
                </select>
                @error('supplier_ids') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium">Fecha límite de respuesta</label>
                <input type="date" wire:model="response_deadline" class="mt-2 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium">Mensaje</label>
                <textarea wire:model="message" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('purchases.quotations', $purchaseRequest) }}" class="rounded-xl border px-4 py-2 text-sm">Volver</a>
            <button type="submit" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white dark:bg-cyan-500 dark:text-slate-950">Enviar</button>
        </div>
    </form>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Invitaciones registradas</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="px-3 py-2">Proveedor</th>
                        <th class="px-3 py-2">Estado</th>
                        <th class="px-3 py-2">Enviado</th>
                        <th class="px-3 py-2">Límite</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invitations as $invitation)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-3 py-3">{{ $invitation->supplier?->business_name }}</td>
                            <td class="px-3 py-3">{{ $invitation->status }}</td>
                            <td class="px-3 py-3">{{ $invitation->sent_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-3">{{ $invitation->response_deadline?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-6 text-slate-500">Sin invitaciones.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
