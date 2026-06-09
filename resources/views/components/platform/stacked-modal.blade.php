@props(['show' => false, 'maxWidth' => 'max-w-2xl'])

@if ($show)
    <div class="platform-modal-stacked-backdrop absolute inset-0 flex items-start justify-center overflow-y-auto bg-slate-950/75 px-4 py-8 backdrop-blur-sm">
        <div
            {{ $attributes->class("relative mt-8 w-full {$maxWidth} rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900 sm:mt-16") }}
            wire:click.stop
        >
            {{ $slot }}
        </div>
    </div>
@endif
