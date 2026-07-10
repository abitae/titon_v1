@props([
    'label',
    'value',
    'percent' => null,
    'tone' => 'slate',
])

@php
    $barColor = match ($tone) {
        'cyan' => 'bg-cyan-500',
        'emerald' => 'bg-emerald-500',
        'rose' => 'bg-rose-500',
        'amber' => 'bg-amber-500',
        default => 'bg-slate-500',
    };

    $valueColor = match ($tone) {
        'cyan' => 'text-cyan-700 dark:text-cyan-300',
        'emerald' => 'text-emerald-700 dark:text-emerald-300',
        'rose' => 'text-rose-700 dark:text-rose-300',
        'amber' => 'text-amber-700 dark:text-amber-300',
        default => 'text-slate-900 dark:text-white',
    };
@endphp

<div {{ $attributes->class('rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm dark:border-slate-800 dark:bg-slate-900') }}>
    <div class="flex items-end justify-between gap-2">
        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</p>
        <p class="text-xl font-bold tabular-nums {{ $valueColor }}">{{ $value }}</p>
    </div>
    @if ($percent !== null)
        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
            <div class="{{ $barColor }} h-full rounded-full transition-all" style="width: {{ min(100, max(0, (float) $percent)) }}%"></div>
        </div>
    @endif
</div>
