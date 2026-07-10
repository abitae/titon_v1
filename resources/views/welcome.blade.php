@php
    $branding = app(\App\Services\Branding\PlatformBranding::class);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="flex min-h-svh flex-col items-center justify-center gap-8 p-6">
            <x-platform-brand size="lg" />

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
