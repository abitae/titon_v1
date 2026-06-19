@props([
    'title' => '',
    'subtitle' => null,
    'imageUrl' => null,
])

<section @class([
    'relative overflow-hidden border-b border-slate-200',
    'bg-slate-900 text-white' => filled($imageUrl),
    'bg-slate-50 text-slate-900' => blank($imageUrl),
])>
    @if ($imageUrl)
        <div
            class="absolute inset-0 bg-cover bg-center opacity-40"
            style="background-image: url('{{ $imageUrl }}')"
        ></div>
    @endif

    <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        @if (filled($title))
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ $title }}</h1>
        @endif

        @if ($subtitle)
            <p @class([
                'max-w-3xl text-lg',
                'mt-4' => filled($title),
                'text-slate-200' => filled($imageUrl),
                'text-slate-600' => blank($imageUrl),
            ])>{{ $subtitle }}</p>
        @endif
    </div>
</section>
