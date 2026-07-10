@props(['title', 'description' => null])

<div class="flex flex-wrap items-center justify-between gap-3">
    <div class="min-w-0">
        <p class="text-[10px] font-semibold uppercase tracking-wider text-cyan-700 dark:text-cyan-400">Mecanica</p>
        <h1 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $title }}</h1>
        @if ($description)
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <flux:button variant="outline" href="{{ route('modules.mechanics') }}" wire:navigate size="sm">
            Panel
        </flux:button>
        {{ $slot }}
    </div>
</div>
