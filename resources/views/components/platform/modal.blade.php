@props(['show' => false, 'maxWidth' => 'max-w-4xl', 'compact' => false, 'layer' => 'default'])

@php
    $panelClass = $compact
        ? "relative w-full {$maxWidth} rounded-xl border border-slate-200 bg-white p-4 shadow-xl dark:border-slate-800 dark:bg-slate-900"
        : "relative w-full {$maxWidth} rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900";

    $layerClass = match ($layer) {
        'top' => 'z-[120]',
        default => 'z-50',
    };
@endphp

@if ($show)
    <div @class([
        'platform-modal-backdrop fixed inset-0 flex justify-center overflow-y-auto bg-slate-900/40 backdrop-blur-sm dark:bg-slate-950/60',
        $layerClass,
        'items-start px-3 py-4' => $compact,
        'items-start px-4 py-8' => ! $compact,
    ])>
        <div class="{{ $panelClass }}">
            {{ $slot }}
        </div>

        @isset($stacked)
            {{ $stacked }}
        @endisset
    </div>
@endif
