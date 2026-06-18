<?php

use App\Services\Frontend\SiteContentService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::frontend')]
#[Title('Nosotros')]
class extends Component
{
    public ?object $header = null;

  /**
   * @var \Illuminate\Support\Collection<int, \App\Models\SiteSetting>
   */
    public $sections;

  /**
   * @var \Illuminate\Support\Collection<int, \App\Models\SiteSetting>
   */
    public $stats;

    public function mount(SiteContentService $content): void
    {
        $this->header = $content->section('about.header');
        $this->sections = collect([
            $content->section('about.mission'),
            $content->section('about.vision'),
            $content->section('about.values'),
            $content->section('about.history'),
        ])->filter();
        $this->stats = $content->sectionsByPrefix('about.stats');
    }
};
?>

<div>
    @if ($header)
        <x-frontend.page-header :title="$header->title" :subtitle="$header->subtitle" />
    @endif

    @if ($stats->isNotEmpty())
        <section class="border-b border-slate-200 bg-white py-12">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:grid-cols-3 sm:px-6 lg:px-8">
                @foreach ($stats as $stat)
                    <div class="text-center">
                        <p class="text-4xl font-bold text-cyan-700">{{ $stat->title }}</p>
                        <p class="mt-2 text-sm font-medium uppercase tracking-wider text-slate-600">{{ $stat->subtitle }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="bg-white py-16">
        <div class="mx-auto max-w-7xl space-y-16 px-4 sm:px-6 lg:px-8">
            @foreach ($sections as $section)
                <div class="max-w-3xl">
                    <h2 class="text-2xl font-bold text-slate-900">{{ $section->title }}</h2>
                    @if ($section->body)
                        <p class="mt-4 text-lg leading-relaxed text-slate-600">{{ $section->body }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
</div>
