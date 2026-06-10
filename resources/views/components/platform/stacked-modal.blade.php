@props(['show' => false, 'maxWidth' => 'max-w-2xl', 'compact' => false])

@php
    $panelClass = $compact
        ? "relative mt-4 w-full {$maxWidth} rounded-xl border border-slate-200 bg-white p-4 shadow-xl dark:border-slate-800 dark:bg-slate-900 sm:mt-8"
        : "relative mt-8 w-full {$maxWidth} rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900 sm:mt-16";
@endphp

@if ($show)
    <div @class([
        'platform-modal-stacked-backdrop absolute inset-0 flex items-start justify-center overflow-y-auto bg-slate-950/75 backdrop-blur-sm',
        'px-3 py-4' => $compact,
        'px-4 py-8' => ! $compact,
    ])>
        <div
            {{ $attributes->class($panelClass) }}
            wire:click.stop
        >
            {{ $slot }}
        </div>
    </div>
@endif
