<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'title' => fake()->sentence(4),
            'subtitle' => fake()->sentence(6),
            'body' => fake()->paragraphs(2, true),
            'cta_label' => 'Más información',
            'cta_url' => '/nosotros',
            'image_path' => null,
            'favicon_path' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
