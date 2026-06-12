@props([
    'variant' => 'default',
])

@php
    $variantClass = match ($variant) {
        'primary' => 'font-semibold text-slate-950 dark:text-white',
        'actions' => '!px-1.5 !py-1 whitespace-nowrap',
        'empty' => '!px-2.5 !py-5 text-center text-[11px] text-slate-500 dark:text-slate-400',
        'numeric' => 'tabular-nums whitespace-nowrap',
        default => '',
    };
@endphp

<td {{ $attributes->class($variantClass) }}>{{ $slot }}</td>
