@props([
    'label' => null,
    'options' => [],
    'optionValue' => 'id',
    'optionLabel' => 'label',
    'optionSecondary' => null,
    'searchModel' => 'search',
    'selectedValue' => '',
    'selectMethod' => 'selectOption',
    'placeholder' => 'Buscar...',
    'emptyText' => 'Sin resultados',
    'error' => null,
])

<div
    x-data="{ open: false }"
    class="relative"
    @click.outside="open = false"
>
    @if ($label)
        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $label }}</label>
    @endif

    <input
        type="text"
        wire:model.live.debounce.300ms="{{ $searchModel }}"
        @focus="open = true"
        @keydown.escape="open = false"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        @class([
            'mt-1 block h-8 w-full rounded-lg border bg-white px-2 text-xs dark:bg-slate-950 dark:text-white',
            'border-rose-500' => filled($error),
            'border-slate-300 dark:border-slate-700' => blank($error),
        ])
    />

    <div
        x-show="open"
        x-cloak
        class="absolute z-[130] mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900"
    >
        @forelse ($options as $option)
            @php
                $value = is_array($option) ? $option[$optionValue] : $option->{$optionValue};
                $text = is_array($option) ? $option[$optionLabel] : $option->{$optionLabel};
                $secondary = $optionSecondary
                    ? (is_array($option) ? ($option[$optionSecondary] ?? null) : $option->{$optionSecondary})
                    : null;
                $isSelected = (string) $value === (string) $selectedValue;
            @endphp
            <button
                type="button"
                wire:click="{{ $selectMethod }}({{ $value }})"
                @click="open = false"
                @class([
                    'flex w-full items-center justify-between gap-2 px-2.5 py-1.5 text-left text-xs transition',
                    'bg-cyan-50 text-cyan-900 dark:bg-cyan-950/50 dark:text-cyan-200' => $isSelected,
                    'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800' => ! $isSelected,
                ])
            >
                <span class="truncate font-medium">{{ $text }}</span>
                @if (filled($secondary))
                    <span class="shrink-0 text-[10px] text-slate-500 dark:text-slate-400">{{ $secondary }}</span>
                @endif
            </button>
        @empty
            <p class="px-2.5 py-2 text-[11px] text-slate-500 dark:text-slate-400">{{ $emptyText }}</p>
        @endforelse
    </div>

    @if ($error)
        <p class="mt-0.5 text-[11px] text-rose-600">{{ $error }}</p>
    @endif
</div>
