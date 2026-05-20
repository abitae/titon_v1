@props(['title' => null])

@php
    $applicationSettings = app(\App\Services\Application\ApplicationSettingsManager::class);
    $headerCompany = auth()->check()
        ? app(\App\Actions\Companies\ResolveCurrentCompany::class)->handle(auth()->user())
        : null;
    $headerCompanies = auth()->user()?->activeCompanies()->orderBy('companies.name')->get() ?? collect();

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
                            {{ $applicationSettings->appName() }}
                        </p>
                        <flux:heading size="xl" level="1" class="truncate text-slate-900 dark:text-white">
                            {{ $pageHeading }}
                        </flux:heading>
                    </div>
                </div>

                <div class="hidden min-w-0 items-center gap-3 lg:flex">
                    <div class="min-w-0 rounded-2xl border border-slate-200/90 bg-white px-3 py-2 shadow-sm shadow-slate-900/[0.05] ring-1 ring-slate-900/[0.04] dark:border-slate-700/80 dark:bg-slate-900/80 dark:shadow-none dark:ring-white/[0.06]">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                            Empresa activa
                        </p>
                        <p class="mt-1 max-w-[17rem] truncate text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $headerCompany?->name ?? 'Sin empresa activa' }}
                        </p>
                        @if ($headerCompany?->business_name && $headerCompany->business_name !== $headerCompany->name)
                            <p class="max-w-[17rem] truncate text-[11px] text-slate-500 dark:text-slate-400">
                                {{ $headerCompany->business_name }}
                            </p>
                        @endif
                    </div>

                    @if ($headerCompanies->isNotEmpty())
                        <form method="POST" action="{{ route('active-company.store') }}" class="flex items-center gap-2">
                            @csrf
                            <label class="sr-only" for="header-company-id">{{ __('Empresa activa') }}</label>
                            <select
                                id="header-company-id"
                                name="company_id"
                                class="min-w-[13rem] rounded-xl border border-slate-200/90 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/25 dark:[color-scheme:dark]"
                            >
                                @foreach ($headerCompanies as $company)
                                    <option value="{{ $company->id }}" @selected($headerCompany?->is($company))>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>

                            <flux:button type="submit" variant="primary" size="sm">
                                Aplicar
                            </flux:button>
                        </form>
                    @endif

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

                    @if ($headerCompany)
                        <flux:badge
                            color="zinc"
                            size="sm"
                            class="max-w-[12rem] truncate !border-slate-200/90 !bg-slate-100 !text-slate-800 dark:!border-slate-700 dark:!bg-slate-800/90 dark:!text-slate-200"
                        >
                            <span class="truncate">{{ $headerCompany->name }}</span>
                        </flux:badge>
                    @endif

                    <x-app-user-dropdown />
                </div>
            </div>
        </flux:header>

        @if ($headerCompanies->isNotEmpty())
            <div class="border-b border-slate-200/90 bg-white/95 px-4 py-3 shadow-sm shadow-slate-900/[0.03] backdrop-blur-md dark:border-slate-800/90 dark:bg-slate-950/90 dark:shadow-none lg:hidden">
                <form method="POST" action="{{ route('active-company.store') }}" class="flex items-center gap-2">
                    @csrf
                    <label class="sr-only" for="mobile-header-company-id">{{ __('Empresa activa') }}</label>
                    <select
                        id="mobile-header-company-id"
                        name="company_id"
                        class="min-w-0 flex-1 rounded-xl border border-slate-200/90 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-cyan-400 dark:focus:ring-cyan-400/25 dark:[color-scheme:dark]"
                    >
                        @foreach ($headerCompanies as $company)
                            <option value="{{ $company->id }}" @selected($headerCompany?->is($company))>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>

                    <flux:button type="submit" variant="primary" size="sm">
                        Aplicar
                    </flux:button>
                </form>
            </div>
        @endif

        <div class="flex flex-1 flex-col overflow-x-hidden overflow-y-auto px-4 pb-10 pt-4 sm:px-6 lg:px-8 lg:pt-6">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts::app.sidebar>
