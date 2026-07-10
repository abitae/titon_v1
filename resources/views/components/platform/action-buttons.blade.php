@props([
    'edit' => null,
    'editHref' => null,
    'editNavigate' => true,
    'delete' => null,
    'deleteUrl' => null,
    'deleteConfirm' => '¿Eliminar este registro?',
])

<div {{ $attributes->merge(['class' => 'flex items-center justify-end gap-0']) }}>
    @if ($edit || $editHref)
        <flux:tooltip content="Editar">
            @if ($editHref)
                @if ($editNavigate)
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="pencil-square"
                        href="{{ $editHref }}"
                        wire:navigate
                        class="!size-7 !min-h-0 !p-0"
                        aria-label="Editar"
                    ></flux:button>
                @else
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="pencil-square"
                        href="{{ $editHref }}"
                        class="!size-7 !min-h-0 !p-0"
                        aria-label="Editar"
                    ></flux:button>
                @endif
            @else
                <flux:button
                    type="button"
                    variant="ghost"
                    size="sm"
                    icon="pencil-square"
                    wire:click="{{ $edit }}"
                    class="!size-7 !min-h-0 !p-0"
                    aria-label="Editar"
                />
            @endif
        </flux:tooltip>
    @endif

    @if ($delete)
        <flux:tooltip content="Eliminar">
            <flux:button
                type="button"
                variant="ghost"
                size="sm"
                icon="trash"
                wire:click="{{ $delete }}"
                wire:confirm="{{ $deleteConfirm }}"
                class="!size-7 !min-h-0 !p-0 !text-rose-600 hover:!text-rose-700 dark:!text-rose-400 dark:hover:!text-rose-300"
                aria-label="Eliminar"
            />
        </flux:tooltip>
    @elseif ($deleteUrl)
        <form method="POST" action="{{ $deleteUrl }}" class="inline" onsubmit="return confirm(@js($deleteConfirm))">
            @csrf
            @method('DELETE')
            <flux:tooltip content="Eliminar">
                <flux:button
                    type="submit"
                    variant="ghost"
                    size="sm"
                    icon="trash"
                    class="!size-7 !min-h-0 !p-0 !text-rose-600 hover:!text-rose-700 dark:!text-rose-400 dark:hover:!text-rose-300"
                    aria-label="Eliminar"
                />
            </flux:tooltip>
        </form>
    @endif
</div>
