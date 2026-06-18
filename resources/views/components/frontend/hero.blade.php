@props([
    'title' => '',
    'subtitle' => null,
    'ctaLabel' => null,
    'ctaUrl' => null,
    'imageUrl' => null,
])

<section class="relative overflow-hidden bg-slate-900 text-white">
    @if ($imageUrl)
        <div
            class="absolute inset-0 bg-cover bg-center opacity-40"
            style="background-image: url('{{ $imageUrl }}')"
        ></div>
    @else
        <div class="absolute inset-0 bg-linear-to-br from-slate-900 via-slate-800 to-cyan-900 opacity-90"></div>
    @endif

    <div class="relative mx-auto flex min-h-[28rem] max-w-7xl flex-col justify-center px-4 py-20 sm:px-6 lg:min-h-[32rem] lg:px-8">
        <div class="max-w-3xl">
            <h1 class="text-4xl font-bold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                {{ $title }}
            </h1>

            @if ($subtitle)
                <p class="mt-6 text-lg leading-relaxed text-slate-200 sm:text-xl">
                    {{ $subtitle }}
                </p>
            @endif

            @if ($ctaLabel && $ctaUrl)
                <div class="mt-10">
                    <a
                        href="{{ $ctaUrl }}"
                        class="inline-flex items-center rounded-lg bg-cyan-600 px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-cyan-500"
                        wire:navigate
                    >
                        {{ $ctaLabel }}
                        <flux:icon name="arrow-right" class="ms-2 size-4" />
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>
