@props([
    'sidebar' => false,
])

@php
    $applicationSettings = app(\App\Services\Application\ApplicationSettingsManager::class);
    $applicationName = $applicationSettings->appName();
    $applicationLogo = $applicationSettings->logoUrl();
@endphp

@if($sidebar)
    <flux:sidebar.brand name="{{ $applicationName }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-accent-content text-accent-foreground">
            @if ($applicationLogo)
                <img src="{{ $applicationLogo }}" alt="{{ $applicationName }}" class="size-full object-cover" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="{{ $applicationName }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-accent-content text-accent-foreground">
            @if ($applicationLogo)
                <img src="{{ $applicationLogo }}" alt="{{ $applicationName }}" class="size-full object-cover" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:brand>
@endif
