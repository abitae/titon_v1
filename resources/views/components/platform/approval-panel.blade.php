@props([
    'approveLabel' => 'Aprobar',
    'cancelLabel' => 'Anular',
    'approveWireClick' => null,
    'cancelWireClick' => null,
    'notesModel' => 'approval_notes',
    'reasonModel' => 'cancellation_reason',
])

<div {{ $attributes->merge(['class' => 'grid gap-4 md:grid-cols-2']) }}>
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Aprobación</h3>
        <textarea wire:model="{{ $notesModel }}" rows="3" class="mt-4 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Notas de aprobación"></textarea>
        @if ($approveWireClick)
            <button type="button" wire:click="{{ $approveWireClick }}" class="mt-4 w-full rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">{{ $approveLabel }}</button>
        @endif
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Anulación</h3>
        <textarea wire:model="{{ $reasonModel }}" rows="3" class="mt-4 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Motivo de anulación"></textarea>
        @error($reasonModel) <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
        @if ($cancelWireClick)
            <button type="button" wire:click="{{ $cancelWireClick }}" class="mt-4 w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300">{{ $cancelLabel }}</button>
        @endif
    </div>
</div>
