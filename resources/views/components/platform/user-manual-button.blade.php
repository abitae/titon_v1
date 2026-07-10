@php
    $manual = app(\App\Services\UserManuals\UserManualCatalog::class)->forRoute(request()->route()?->getName());
@endphp

@if ($manual !== null)
    <div
        x-data="{ open: false }"
        x-on:keydown.escape.window="open = false"
        class="pointer-events-none fixed inset-x-0 bottom-4 z-40 flex justify-end px-4 sm:bottom-6 sm:px-6 lg:px-8"
        data-test="user-manual-widget"
    >
        <flux:tooltip content="Manual de usuario" position="left">
            <flux:button
                type="button"
                variant="primary"
                icon="book-open"
                class="pointer-events-auto !rounded-full !px-4 shadow-xl shadow-slate-900/20"
                aria-label="Abrir manual de usuario"
                data-test="user-manual-button"
                x-on:click="open = true"
            >
                Manual
            </flux:button>
        </flux:tooltip>

        <div
            x-cloak
            x-show="open"
            x-transition.opacity
            class="pointer-events-auto fixed inset-0 z-[45] flex items-end justify-center bg-slate-900/40 px-3 py-4 backdrop-blur-sm dark:bg-slate-950/60 sm:items-center sm:px-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="user-manual-title"
            data-test="user-manual-modal"
        >
            <div
                x-show="open"
                x-transition.scale.origin.bottom.right
                x-on:click.outside="open = false"
                class="flex max-h-[88dvh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-4 py-3 dark:border-slate-800 sm:px-5">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-cyan-700 dark:text-cyan-400">
                            {{ $manual['module'] }}
                        </p>
                        <h2 id="user-manual-title" class="mt-1 text-lg font-semibold text-slate-950 dark:text-white">
                            {{ $manual['title'] }}
                        </h2>
                    </div>

                    <flux:tooltip content="Cerrar">
                        <flux:button
                            type="button"
                            variant="ghost"
                            size="sm"
                            icon="x-mark"
                            class="!size-8 !min-h-0 !p-0"
                            aria-label="Cerrar manual de usuario"
                            x-on:click="open = false"
                        />
                    </flux:tooltip>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-5">
                    <section class="rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-3 text-sm leading-6 text-cyan-950 dark:border-cyan-900/70 dark:bg-cyan-950/35 dark:text-cyan-100">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-cyan-800 dark:text-cyan-300">Proposito</h3>
                        <p class="mt-1">{{ $manual['purpose'] }}</p>
                    </section>

                    <div class="mt-4 grid gap-3 lg:grid-cols-2">
                        @foreach ($manual['sections'] as $section)
                            <section class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-950/40">
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                    {{ $section['title'] }}
                                </h3>

                                @if (($section['ordered'] ?? false) === true)
                                    <ol class="mt-2 list-decimal space-y-1.5 ps-4 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                        @foreach ($section['items'] as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ol>
                                @else
                                    <ul class="mt-2 list-disc space-y-1.5 ps-4 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                        @foreach ($section['items'] as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </section>
                        @endforeach
                    </div>

                    @if (($manual['permissions'] ?? []) !== [])
                        <section class="mt-4 rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Permisos relacionados</h3>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($manual['permissions'] as $permission)
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] font-medium text-slate-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                                        {{ $permission }}
                                    </span>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
