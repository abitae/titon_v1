<?php

use App\Services\Frontend\SiteContentService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::frontend')]
#[Title('Proyectos')]
class extends Component
{
    public ?object $header = null;

    public string $cityFilter = '';

  /**
   * @var \Illuminate\Support\Collection<int, \App\Models\ShowcaseProject>
   */
    public $projects;

  /**
   * @var list<string>
   */
    public array $cities = [];

    public function mount(SiteContentService $content): void
    {
        $this->header = $content->section('projects.header');
        $this->cities = $content->publishedCities();
        $this->loadProjects($content);
    }

    public function updatedCityFilter(SiteContentService $content): void
    {
        $this->loadProjects($content);
    }

    protected function loadProjects(SiteContentService $content): void
    {
        $this->projects = $content->publishedProjects(
            filled($this->cityFilter) ? $this->cityFilter : null,
        );
    }
};
?>

<div>
    @if ($header)
        <x-frontend.page-header :title="$header->title" :subtitle="$header->subtitle" />
    @endif

    <section class="bg-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (count($cities) > 0)
                <div class="mb-10 flex flex-wrap items-center gap-3">
                    <flux:select wire:model.live="cityFilter" class="max-w-xs">
                        <option value="">Todas las ciudades</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city }}">{{ $city }}</option>
                        @endforeach
                    </flux:select>
                </div>
            @endif

            @if ($projects->isEmpty())
                <p class="text-center text-slate-600">No hay proyectos publicados en este momento.</p>
            @else
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                            <div class="flex h-48 items-center justify-center bg-linear-to-br from-slate-700 to-cyan-800">
                                @if ($project->imageUrl())
                                    <img src="{{ $project->imageUrl() }}" alt="{{ $project->title }}" class="size-full object-cover" />
                                @else
                                    <flux:icon name="building-office-2" class="size-16 text-white/40" />
                                @endif
                            </div>
                            <div class="p-6">
                                @if ($project->city)
                                    <p class="text-xs font-semibold uppercase tracking-wider text-cyan-700">{{ $project->city }}</p>
                                @endif
                                <h3 class="mt-2 text-lg font-bold text-slate-900">{{ $project->title }}</h3>
                                @if ($project->client_name)
                                    <p class="mt-1 text-sm text-slate-500">{{ $project->client_name }}</p>
                                @endif
                                @if ($project->summary)
                                    <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $project->summary }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
