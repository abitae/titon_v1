@props(['title' => null])

@php
    $siteContent = app(\App\Services\Frontend\SiteContentService::class);
    $pageHeading = filled($title) ? $title : __('Inicio');
@endphp

<x-layouts::app.sidebar :title="$title">
    <flux:main class="flex min-h-[100dvh] flex-col bg-slate-50/90 dark:bg-slate-950/50">
        <flux:header
            sticky
            class="isolate z-40 flex shrink-0 border-b border-slate-200/90 bg-white/95 shadow-sm shadow-slate-900/[0.04] backdrop-blur-md supports-[backdrop-filter]:bg-white/85 dark:border-slate-800/90 dark:bg-slate-950/90 dark:shadow-none dark:supports-[backdrop-filter]:bg-slate-950/80"
        >
            <div class="flex min-h-16 min-w-0 flex-1 items-center gap-3 px-4 sm:px-6 lg:gap-5">
                <flux:sidebar.toggle
                    class="lg:hidden text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-white"
                    icon="bars-2"
                    inset="left"
                />

                <div class="min-w-0 flex-1">
                    <div class="flex min-w-0 flex-col gap-0.5">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-cyan-700 dark:text-cyan-400">
                            {{ $siteContent->brandName() }}
                        </p>
                        <flux:heading size="xl" level="1" class="truncate text-slate-900 dark:text-white">
                            {{ $pageHeading }}
                        </flux:heading>
                    </div>
                </div>

                <div class="hidden min-w-0 items-center gap-3 lg:flex">
                    <x-header-theme-toggle />

                    <flux:tooltip :content="__('Settings')" position="bottom">
                        <flux:navbar class="mx-1">
                            <flux:navbar.item
                                class="!p-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-white"
                                icon="cog-6-tooth"
                                :href="route('profile.edit')"
                                :label="__('Settings')"
                                wire:navigate
                            />
                        </flux:navbar>
                    </flux:tooltip>

                    <x-app-user-dropdown />
                </div>

                <div class="flex items-center gap-2 lg:hidden">
                    <x-header-theme-toggle />

                    <x-app-user-dropdown />
                </div>
            </div>
        </flux:header>

        <div class="flex flex-1 flex-col overflow-x-hidden overflow-y-auto px-4 pb-10 pt-4 sm:px-6 lg:px-8 lg:pt-6">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts::app.sidebar>
