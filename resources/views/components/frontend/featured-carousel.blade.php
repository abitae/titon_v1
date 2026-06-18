@props([
    'projects' => collect(),
])

@if ($projects->isNotEmpty())
    <section class="bg-slate-50 py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">Proyectos destacados</h2>
                    <p class="mt-2 text-slate-600">Obras que reflejan nuestra experiencia en infraestructura.</p>
                </div>
                <a href="{{ route('frontend.projects') }}" class="hidden text-sm font-semibold text-cyan-700 hover:text-cyan-600 sm:inline-flex" wire:navigate>
                    Ver todos
                    <flux:icon name="arrow-right" class="ms-1 size-4" />
                </a>
            </div>

            <div
                x-data="{
                    current: 0,
                    total: {{ $projects->count() }},
                    next() { this.current = (this.current + 1) % this.total },
                    prev() { this.current = (this.current - 1 + this.total) % this.total },
                }"
                class="relative"
            >
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    @foreach ($projects as $index => $project)
                        <div
                            x-show="current === {{ $index }}"
                            x-cloak
                            class="grid lg:grid-cols-2"
                        >
                            <div class="flex h-64 items-center justify-center bg-linear-to-br from-slate-700 to-cyan-800 lg:h-auto lg:min-h-80">
                                @if ($project->imageUrl())
                                    <img src="{{ $project->imageUrl() }}" alt="{{ $project->title }}" class="size-full object-cover" />
                                @else
                                    <flux:icon name="building-office-2" class="size-20 text-white/40" />
                                @endif
                            </div>
                            <div class="flex flex-col justify-center p-8 lg:p-12">
                                @if ($project->city)
                                    <p class="text-sm font-semibold uppercase tracking-wider text-cyan-700">{{ $project->city }}</p>
                                @endif
                                <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ $project->title }}</h3>
                                @if ($project->client_name)
                                    <p class="mt-1 text-sm text-slate-500">Cliente: {{ $project->client_name }}</p>
                                @endif
                                @if ($project->summary)
                                    <p class="mt-4 text-slate-600">{{ $project->summary }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($projects->count() > 1)
                    <div class="mt-6 flex items-center justify-center gap-4">
                        <button
                            type="button"
                            @click="prev()"
                            class="rounded-full border border-slate-300 p-2 text-slate-600 hover:bg-white"
                            aria-label="Anterior"
                        >
                            <flux:icon name="chevron-left" class="size-5" />
                        </button>
                        <div class="flex gap-2">
                            @foreach ($projects as $index => $project)
                                <button
                                    type="button"
                                    @click="current = {{ $index }}"
                                    :class="current === {{ $index }} ? 'bg-cyan-600' : 'bg-slate-300'"
                                    class="size-2.5 rounded-full transition"
                                    aria-label="Ir al proyecto {{ $index + 1 }}"
                                ></button>
                            @endforeach
                        </div>
                        <button
                            type="button"
                            @click="next()"
                            class="rounded-full border border-slate-300 p-2 text-slate-600 hover:bg-white"
                            aria-label="Siguiente"
                        >
                            <flux:icon name="chevron-right" class="size-5" />
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
