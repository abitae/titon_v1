<?php

namespace Database\Factories;

use App\Models\ShowcaseProject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShowcaseProject>
 */
class ShowcaseProjectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'summary' => fake()->sentence(12),
            'description' => fake()->paragraphs(3, true),
            'city' => fake()->city(),
            'client_name' => fake()->company(),
            'image_path' => null,
            'is_published' => true,
            'is_featured' => false,
            'sort_order' => 0,
            'published_at' => now(),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (): array => [
            'is_featured' => true,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }
}
