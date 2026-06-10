@php
    $applicationSettings = app(\App\Services\Application\ApplicationSettingsManager::class);
    $applicationName = $applicationSettings->appName();
    $applicationLogo = $applicationSettings->logoUrl();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="flex min-h-svh flex-col items-center justify-center gap-8 p-6">
            <div class="flex flex-col items-center gap-4 text-center">
                <span class="flex size-14 items-center justify-center overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    @if ($applicationLogo)
                        <img src="{{ $applicationLogo }}" alt="{{ $applicationName }}" class="size-full object-cover" />
                    @else
                        <x-app-logo-icon class="size-8 fill-current text-zinc-900 dark:text-white" />
                    @endif
                </span>

                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                        {{ $applicationName }}
                    </h1>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Welcome') }}
                    </p>
                </div>
            </div>

            @auth
                <flux:button :href="route('dashboard')" variant="primary" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:button>
            @else
                @if (Route::has('login'))
                    <flux:button :href="route('login')" variant="primary" wire:navigate>
                        {{ __('Log in') }}
                    </flux:button>
                @endif
            @endauth
        </div>

        @fluxScripts
    </body>
</html>
