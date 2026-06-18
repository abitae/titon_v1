@props([
    'cards' => [],
])

<section class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($cards as $card)
                <a
                    href="{{ $card->cta_url ?? '#' }}"
                    class="group relative flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-cyan-200 hover:shadow-md"
                    wire:navigate
                >
                    <div class="relative flex h-40 items-end bg-linear-to-br from-slate-800 to-cyan-800 p-6">
                        @if ($card->imageUrl())
                            <img src="{{ $card->imageUrl() }}" alt="" class="absolute inset-0 size-full object-cover opacity-60" />
                        @endif
                        <h3 class="relative text-lg font-semibold text-white">{{ $card->title }}</h3>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        @if ($card->subtitle)
                            <p class="flex-1 text-sm leading-relaxed text-slate-600">{{ $card->subtitle }}</p>
                        @endif
                        <span class="mt-4 inline-flex items-center text-sm font-semibold text-cyan-700 group-hover:text-cyan-600">
                            {{ $card->cta_label ?? 'Más información' }}
                            <flux:icon name="arrow-right" class="ms-1 size-4" />
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
