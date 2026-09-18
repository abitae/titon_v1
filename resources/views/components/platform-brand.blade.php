@props([
    'size' => 'md',
    'showName' => true,
])

@php
    $branding = app(\App\Services\Branding\PlatformBranding::class);
    $name = $branding->name();
    $longLogo = $branding->longLogoUrl();

    $logoHeight = match ($size) {
        'sm' => 'h-10',
        'lg' => 'h-16',
        default => 'h-12',
    };
@endphp

<a
    href="{{ route('home') }}"
    {{ $attributes->class([
        'group flex flex-col items-center gap-3 font-medium',
        'text-center' => $showName,
    ]) }}
    wire:navigate
>
    <span class="flex items-center justify-center overflow-hidden rounded-2xl border border-zinc-200 bg-white px-3 py-2 shadow-sm transition group-hover:border-zinc-300 dark:border-zinc-700 dark:bg-white dark:group-hover:border-zinc-500">
        <img src="{{ $longLogo }}" alt="{{ $name }}" @class([$logoHeight, 'w-auto max-w-[14rem] object-contain']) />
    </span>

    @if ($showName)
        <span class="text-base font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $name }}</span>
    @else
        <span class="sr-only">{{ $name }}</span>
    @endif
</a>
