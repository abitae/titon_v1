<?php

use App\Services\Frontend\SiteContentService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::frontend')]
#[Title('Inicio')]
class extends Component
{
    public ?object $hero = null;

    public ?object $intro = null;

    public ?object $services = null;

    public ?object $cta = null;

  /**
   * @var \Illuminate\Support\Collection<int, \App\Models\ShowcaseProject>
   */
    public $featuredProjects;

    public function mount(SiteContentService $content): void
    {
        $this->hero = $content->section('home.hero');
        $this->intro = $content->section('home.intro');
        $this->services = $content->section('home.services');
        $this->cta = $content->section('home.cta');
        $this->featuredProjects = $content->featuredProjects();
    }

    /**
     * @return Collection<int, \App\Models\SiteSetting>
     */
    #[Computed]
    public function cards(): Collection
    {
        return app(SiteContentService::class)->sectionsByPrefix('home.cards');
    }
};
?>

<div>
    @if ($hero)
        <x-frontend.hero
            :title="$hero->title"
            :subtitle="$hero->subtitle"
            :cta-label="$hero->cta_label"
            :cta-url="$hero->cta_url"
            :image-url="$hero->imageUrl()"
        />
    @endif

    @if ($intro)
        <x-frontend.split-content
            :title="$intro->title"
            :body="$intro->body"
            :cta-label="$intro->cta_label"
            :cta-url="$intro->cta_url"
            :image-url="$intro->imageUrl()"
        />
    @endif

    @if ($this->cards->isNotEmpty())
        <x-frontend.section-cards :cards="$this->cards" />
    @endif

    <x-frontend.featured-carousel :projects="$featuredProjects" />

    @if ($services)
        <x-frontend.split-content
            :title="$services->title"
            :subtitle="$services->subtitle"
            :body="$services->body"
            :cta-label="$services->cta_label"
            :cta-url="$services->cta_url"
            :image-url="$services->imageUrl()"
            :reversed="true"
        />
    @endif

    @if ($cta)
        <section class="bg-slate-900 py-16 text-white">
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold">{{ $cta->title }}</h2>
                @if ($cta->subtitle)
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-300">{{ $cta->subtitle }}</p>
                @endif
                @if ($cta->cta_label && $cta->cta_url)
                    <a
                        href="{{ $cta->cta_url }}"
                        class="mt-8 inline-flex items-center rounded-lg bg-cyan-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-cyan-500"
                        wire:navigate
                    >
                        {{ $cta->cta_label }}
                    </a>
                @endif
            </div>
        </section>
    @endif
</div>
