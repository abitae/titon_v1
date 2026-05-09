<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
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
            'code' => strtoupper(fake()->bothify('OBR-###')),
            'name' => fake()->sentence(3),
            'city' => fake()->city(),
            'address' => fake()->streetAddress(),
            'client_name' => fake()->company(),
            'responsible_user_id' => User::factory(),
            'start_date' => fake()->dateTimeBetween('-3 months', '+1 month'),
            'estimated_end_date' => fake()->dateTimeBetween('+2 months', '+8 months'),
            'estimated_budget' => fake()->randomFloat(2, 150000, 1500000),
            'status' => fake()->randomElement(ProjectStatus::values()),
            'description' => fake()->paragraph(),
        ];
    }
}
