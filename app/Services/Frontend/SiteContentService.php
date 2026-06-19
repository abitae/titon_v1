<?php

namespace App\Services\Frontend;

use App\Models\ShowcaseProject;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class SiteContentService
{
    public function brand(): ?SiteSetting
    {
        return $this->section('brand');
    }

    public function brandName(): string
    {
        return $this->brand()?->displayName() ?? config('app.name', 'Titon');
    }

    public function brandLogoUrl(): ?string
    {
        return $this->brand()?->imageUrl();
    }

    public function brandFaviconUrl(): ?string
    {
        return $this->brand()?->faviconUrl();
    }

    public function section(string $key): ?SiteSetting
    {
        $cacheKey = $this->sectionCacheKey($key);
        $cached = Cache::get($cacheKey);

        if ($cached instanceof SiteSetting) {
            return $cached;
        }

        if (is_array($cached)) {
            return (new SiteSetting)->newFromBuilder($cached);
        }

        if ($cached !== null) {
            Cache::forget($cacheKey);
        }

        $setting = SiteSetting::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        Cache::forever($cacheKey, $setting ? $this->cacheableSiteSettingAttributes($setting) : null);

        return $setting;
    }

    /**
     * @return Collection<int, SiteSetting>
     */
    public function sectionsByPrefix(string $prefix): Collection
    {
        $cacheKey = $this->prefixCacheKey($prefix);
        $cached = Cache::get($cacheKey);

        if ($cached instanceof Collection && ($cached->isEmpty() || $cached->first() instanceof SiteSetting)) {
            return $cached;
        }

        if (is_array($cached)) {
            return SiteSetting::hydrate($cached);
        }

        if ($cached !== null) {
            Cache::forget($cacheKey);
        }

        $settings = SiteSetting::query()
            ->where('key', 'like', $prefix.'.%')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        Cache::forever(
            $cacheKey,
            $settings->map(fn (SiteSetting $setting): array => $this->cacheableSiteSettingAttributes($setting))->all(),
        );

        return $settings;
    }

    /**
     * @return Collection<int, ShowcaseProject>
     */
    public function featuredProjects(): Collection
    {
        $cacheKey = 'site-content.featured-projects';
        $cached = Cache::get($cacheKey);

        if ($cached instanceof Collection && ($cached->isEmpty() || $cached->first() instanceof ShowcaseProject)) {
            return $cached;
        }

        if (is_array($cached)) {
            return ShowcaseProject::hydrate($cached);
        }

        if ($cached !== null) {
            Cache::forget($cacheKey);
        }

        $projects = ShowcaseProject::query()
            ->published()
            ->featured()
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        Cache::forever(
            $cacheKey,
            $projects->map(fn (ShowcaseProject $project): array => $this->cacheableShowcaseProjectAttributes($project))->all(),
        );

        return $projects;
    }

    /**
     * @return Collection<int, ShowcaseProject>
     */
    public function publishedProjects(?string $city = null): Collection
    {
        return ShowcaseProject::query()
            ->published()
            ->when(filled($city), fn ($query) => $query->where('city', $city))
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->get();
    }

    /**
     * @return list<string>
     */
    public function publishedCities(): array
    {
        return ShowcaseProject::query()
            ->published()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->all();
    }

    public function forgetSection(string $key): void
    {
        Cache::forget($this->sectionCacheKey($key));

        $segments = explode('.', $key);
        $prefix = '';

        for ($index = 0; $index < count($segments) - 1; $index++) {
            $prefix = $prefix === '' ? $segments[$index] : $prefix.'.'.$segments[$index];
            Cache::forget($this->prefixCacheKey($prefix));
        }
    }

    public function forgetAll(): void
    {
        SiteSetting::query()->pluck('key')->each(fn (string $key): mixed => $this->forgetSection($key));
        Cache::forget('site-content.featured-projects');
    }

    protected function sectionCacheKey(string $key): string
    {
        return 'site-content.section.'.$key;
    }

    protected function prefixCacheKey(string $prefix): string
    {
        return 'site-content.prefix.'.$prefix;
    }

    /**
     * @return array<string, mixed>
     */
    protected function cacheableSiteSettingAttributes(SiteSetting $setting): array
    {
        return $setting->only([
            'id',
            'key',
            'title',
            'subtitle',
            'body',
            'cta_label',
            'cta_url',
            'image_path',
            'favicon_path',
            'is_active',
            'sort_order',
            'created_at',
            'updated_at',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function cacheableShowcaseProjectAttributes(ShowcaseProject $project): array
    {
        return $project->only([
            'id',
            'title',
            'slug',
            'summary',
            'description',
            'city',
            'client_name',
            'image_path',
            'is_published',
            'is_featured',
            'sort_order',
            'published_at',
            'created_at',
            'updated_at',
        ]);
    }
}
