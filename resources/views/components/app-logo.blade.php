@props([
    'sidebar' => false,
])

@php
    $branding = app(\App\Services\Branding\PlatformBranding::class);
    $applicationName = $branding->name();
    $shortLogo = $branding->shortLogoUrl();
    $longLogo = $branding->longLogoUrl();
@endphp

@if($sidebar)
    <flux:sidebar.brand name="{{ $applicationName }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-white">
            <img src="{{ $shortLogo }}" alt="{{ $applicationName }}" class="size-full object-contain p-0.5" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <a href="{{ $attributes->get('href', route('dashboard')) }}" {{ $attributes->except('href')->class('flex min-w-0 items-center gap-2') }}>
        <img src="{{ $longLogo }}" alt="{{ $applicationName }}" class="h-8 w-auto max-w-[11rem] object-contain sm:h-9" />
        <span class="truncate text-sm font-semibold tracking-tight text-slate-900 dark:text-white">{{ $applicationName }}</span>
    </a>
@endif
