<?php

namespace App\Services\Application;

use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ApplicationSettingsManager
{
    public const CACHE_KEY = 'application-settings.current';

    public function current(): ApplicationSetting
    {
        $cached = Cache::get(self::CACHE_KEY);

        if ($cached instanceof ApplicationSetting) {
            return $cached;
        }

        if (is_array($cached)) {
            return (new ApplicationSetting)->newFromBuilder($cached);
        }

        if ($cached !== null) {
            Cache::forget(self::CACHE_KEY);
        }

        $settings = $this->persisted();

        Cache::forever(self::CACHE_KEY, $this->cacheableAttributes($settings));

        return $settings;
    }

    public function appName(): string
    {
        return $this->current()->application_name ?: config('app.name', 'Titon');
    }

    public function logoUrl(): ?string
    {
        return $this->current()->logoUrl();
    }

    /**
     * @param  array{application_name: string, logo_path?: string|null}  $attributes
     */
    public function update(array $attributes): ApplicationSetting
    {
        $settings = $this->persisted();

        $settings->fill($attributes);
        $settings->save();

        Cache::forget(self::CACHE_KEY);

        return $this->current();
    }

    public function removeLogo(): ApplicationSetting
    {
        $settings = $this->persisted();

        if (filled($settings->logo_path) && Storage::disk('public')->exists($settings->logo_path)) {
            Storage::disk('public')->delete($settings->logo_path);
        }

        $settings->forceFill(['logo_path' => null])->save();

        Cache::forget(self::CACHE_KEY);

        return $this->current();
    }

    protected function persisted(): ApplicationSetting
    {
        return ApplicationSetting::query()->firstOrCreate(
            ['id' => 1],
            ['application_name' => config('app.name', 'Titon')],
        );
    }

    /**
     * @return array{id: int, application_name: string, logo_path: ?string, created_at: mixed, updated_at: mixed}
     */
    protected function cacheableAttributes(ApplicationSetting $settings): array
    {
        /** @var array{id: int, application_name: string, logo_path: ?string, created_at: mixed, updated_at: mixed} */
        return $settings->only([
            'id',
            'application_name',
            'logo_path',
            'created_at',
            'updated_at',
        ]);
    }
}
