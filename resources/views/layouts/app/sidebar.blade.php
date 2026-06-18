@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen min-h-[100dvh] bg-slate-50 antialiased dark:bg-slate-950">
        @php
            $navigation = app(\App\Services\Navigation\AppNavigation::class)->sidebarGroups();
        @endphp

        <flux:sidebar
            sticky
            collapsible="mobile"
            class="flex max-h-dvh min-h-0 flex-col overflow-hidden border-e border-slate-200/90 bg-[radial-gradient(circle_at_0%_0%,rgba(14,165,233,0.09),transparent_52%),linear-gradient(180deg,#f8fafc_0%,#eef2ff_55%,#f1f5f9_100%)] shadow-[2px_0_24px_-12px_rgba(15,23,42,0.35)] lg:h-dvh dark:border-slate-800 dark:bg-[radial-gradient(circle_at_0%_0%,rgba(34,211,238,0.12),transparent_42%),linear-gradient(180deg,#020617_0%,#0f172a_50%,#0c1222_100%)] dark:shadow-[2px_0_28px_-14px_rgba(0,0,0,0.65)]"
        >
            <flux:sidebar.header class="shrink-0 gap-3 border-b border-slate-200/70 pb-4 dark:border-slate-800/80">
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate class="min-w-0 max-w-full" />

                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <div class="min-h-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-y-contain [-ms-overflow-style:none] [scrollbar-gutter:stable] [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-slate-300/80 dark:[&::-webkit-scrollbar-thumb]:bg-slate-600/80">
                <flux:sidebar.nav class="gap-1.5 py-3">
                    @foreach ($navigation as $group)
                        @php
                            $groupExpanded = collect($group['items'])->contains(
                                fn (array $item): bool => (bool) ($item['current'] ?? false),
                            );
                        @endphp

                        <flux:sidebar.group
                            expandable
                            :heading="$group['heading']"
                            :expanded="$groupExpanded"
                            class="grid min-w-0 gap-1"
                        >
                            @foreach ($group['items'] as $item)
                                <flux:sidebar.item
                                    :icon="$item['icon']"
                                    :href="$item['href']"
                                    :current="$item['current']"
                                    class="min-w-0 rounded-xl"
                                    wire:navigate
                                >
                                    <span class="truncate text-sm font-medium">{{ $item['label'] }}</span>
                                </flux:sidebar.item>
                            @endforeach
                        </flux:sidebar.group>
                    @endforeach
                </flux:sidebar.nav>
            </div>

            <flux:sidebar.nav class="shrink-0 border-t border-slate-200/60 bg-gradient-to-t from-slate-50/90 to-transparent pt-2 dark:border-slate-700/60 dark:from-slate-950/90">
                <div class="rounded-xl border border-slate-200/80 bg-white/80 px-2 py-1.5 text-xs text-slate-600 backdrop-blur dark:border-white/10 dark:bg-slate-900/60 dark:text-slate-400">
                    <p class="font-medium text-slate-800 dark:text-slate-200">Atajos</p>
                    <a
                        href="{{ route('settings.catalogs') }}"
                        wire:navigate
                        class="mt-2 inline-flex items-center gap-1.5 text-[11px] font-medium text-cyan-700 hover:text-cyan-600 hover:underline dark:text-cyan-400 dark:hover:text-cyan-300"
                    >
                        <flux:icon.squares-plus variant="outline" class="size-3.5 shrink-0 opacity-90" />
                        Catalogos maestros
                    </a>
                </div>
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        @php($sessionToasts = session('toasts', []))

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @if ($sessionToasts !== [])
            <div
                x-data="{ toasts: @js($sessionToasts) }"
                x-init="$nextTick(() => toasts.forEach((toast) => document.dispatchEvent(new CustomEvent('toast-show', { detail: toast }))))"
            ></div>
        @endif

        @fluxScripts
    </body>
</html>
