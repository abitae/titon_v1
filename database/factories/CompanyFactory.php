<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyName = fake()->unique()->company();

        return [
            'name' => $companyName,
            'correlative_prefix' => null,
            'business_name' => $companyName.' S.A.C.',
            'ruc' => fake()->unique()->numerify('20#########'),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'logo' => null,
            'primary_color' => fake()->randomElement(['#0f172a', '#1d4ed8', '#065f46']),
            'secondary_color' => fake()->randomElement(['#0891b2', '#f59e0b', '#7c3aed']),
            'status' => 'active',
        ];
    }
}
