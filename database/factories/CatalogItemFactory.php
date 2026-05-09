<?php

namespace Database\Factories;

use App\Enums\CatalogType;
use App\Models\CatalogItem;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CatalogItem>
 */
class CatalogItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'type' => fake()->randomElement(CatalogType::values()),
            'name' => fake()->unique()->word(),
            'code' => strtoupper(fake()->bothify('CFG-##')),
            'description' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
