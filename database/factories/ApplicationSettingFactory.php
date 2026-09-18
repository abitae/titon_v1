<?php

namespace Database\Factories;

use App\Models\ApplicationSetting;
use App\Services\Branding\PlatformBranding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationSetting>
 */
class ApplicationSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_name' => PlatformBranding::DEFAULT_NAME,
            'logo_path' => null,
        ];
    }
}
