@props([
    'title' => '',
    'subtitle' => null,
    'body' => null,
    'ctaLabel' => null,
    'ctaUrl' => null,
    'imageUrl' => null,
    'reversed' => false,
])

<section class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div @class([
            'grid items-center gap-12 lg:grid-cols-2',
            'lg:[&>*:first-child]:order-2' => $reversed,
        ])>
            <div>
                @if ($subtitle)
                    <p class="text-sm font-semibold uppercase tracking-wider text-cyan-700">{{ $subtitle }}</p>
                @endif
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $title }}</h2>
                @if ($body)
                    <p class="mt-6 text-lg leading-relaxed text-slate-600">{{ $body }}</p>
                @endif
                @if ($ctaLabel && $ctaUrl)
                    <a
                        href="{{ $ctaUrl }}"
                        class="mt-8 inline-flex items-center text-sm font-semibold text-cyan-700 hover:text-cyan-600"
                        wire:navigate
                    >
                        {{ $ctaLabel }}
                        <flux:icon name="arrow-right" class="ms-1 size-4" />
                    </a>
                @endif
            </div>

            <div class="flex min-h-64 items-center justify-center overflow-hidden rounded-2xl bg-linear-to-br from-slate-800 to-cyan-800">
                @if ($imageUrl)
                    <img src="{{ $imageUrl }}" alt="" class="size-full object-cover" />
                @else
                    <flux:icon name="building-office-2" class="size-24 text-white/30" />
                @endif
            </div>
        </div>
    </div>
</section>
