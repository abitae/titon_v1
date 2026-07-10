@props([
    'sidebar' => false,
])

@php
    $branding = app(\App\Services\Branding\PlatformBranding::class);
    $applicationName = $branding->name();
    $applicationLogo = $branding->logoUrl();
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
