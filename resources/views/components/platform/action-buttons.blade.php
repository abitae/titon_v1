@props([
    'view' => null,
    'edit' => null,
    'delete' => null,
    'deleteConfirm' => '¿Eliminar este registro?',
])

<div class="flex items-center justify-end gap-2 whitespace-nowrap">
    @if ($view)
        <button type="button" wire:click="{{ $view }}" class="rounded-lg px-2 py-1 text-sm font-medium text-cyan-700 hover:bg-cyan-50 hover:text-cyan-600 dark:text-cyan-300 dark:hover:bg-cyan-950/40">
            Ver
        </button>
    @endif

    @if ($edit)
        <button type="button" wire:click="{{ $edit }}" class="rounded-lg px-2 py-1 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">
            Editar
        </button>
    @endif

    @if ($delete)
        <button
            type="button"
            wire:click="{{ $delete }}"
            wire:confirm="{{ $deleteConfirm }}"
            class="rounded-lg px-2 py-1 text-sm font-medium text-rose-700 hover:bg-rose-50 hover:text-rose-600 dark:text-rose-300 dark:hover:bg-rose-950/40"
        >
            Eliminar
        </button>
    @endif
</div>
