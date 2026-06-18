@props([
    'title' => '',
    'subtitle' => null,
])

<section class="border-b border-slate-200 bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-4 max-w-3xl text-lg text-slate-600">{{ $subtitle }}</p>
        @endif
    </div>
</section>
