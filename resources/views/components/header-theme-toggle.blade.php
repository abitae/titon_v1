<div
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5 rounded-xl border border-slate-200/90 bg-slate-100/80 p-0.5 dark:border-slate-700/80 dark:bg-slate-900/90']) }}
    x-data
    role="group"
    aria-label="{{ __('Appearance') }}"
>
    <button
        type="button"
        class="rounded-lg p-2 text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
        :class="$flux.appearance === 'light' && 'bg-white text-cyan-700 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-800 dark:text-cyan-400 dark:ring-slate-600/80'"
        @click="$flux.appearance = 'light'"
        :aria-pressed="$flux.appearance === 'light'"
        title="{{ __('Light') }}"
    >
        <flux:icon.sun variant="mini" class="size-4" />
    </button>
    <button
        type="button"
        class="rounded-lg p-2 text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
        :class="$flux.appearance === 'dark' && 'bg-white text-cyan-700 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-800 dark:text-cyan-400 dark:ring-slate-600/80'"
        @click="$flux.appearance = 'dark'"
        :aria-pressed="$flux.appearance === 'dark'"
        title="{{ __('Dark') }}"
    >
        <flux:icon.moon variant="mini" class="size-4" />
    </button>
    <button
        type="button"
        class="rounded-lg p-2 text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
        :class="$flux.appearance === 'system' && 'bg-white text-cyan-700 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-800 dark:text-cyan-400 dark:ring-slate-600/80'"
        @click="$flux.appearance = 'system'"
        :aria-pressed="$flux.appearance === 'system'"
        title="{{ __('System') }}"
    >
        <flux:icon.computer-desktop variant="mini" class="size-4" />
    </button>
</div>
