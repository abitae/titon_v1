@props([
    'size' => 'md',
    'showName' => true,
])

@php
    $branding = app(\App\Services\Branding\PlatformBranding::class);
    $name = $branding->name();
    $logoUrl = $branding->logoUrl();

    $containerClasses = match ($size) {
        'sm' => 'size-10',
        'lg' => 'size-20',
        default => 'size-14',
    };

    $iconClasses = match ($size) {
        'sm' => 'size-6',
        'lg' => 'size-12',
        default => 'size-8',
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
    <span @class([
        'flex items-center justify-center overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition group-hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:group-hover:border-zinc-600',
        $containerClasses,
    ])>
        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $name }}" class="size-full object-contain p-1.5" />
        @else
            <x-app-logo-icon @class([$iconClasses, 'fill-current text-zinc-900 dark:text-white']) />
        @endif
    </span>

    @if ($showName)
        <span class="text-base font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $name }}</span>
    @else
        <span class="sr-only">{{ $name }}</span>
    @endif
</a>
