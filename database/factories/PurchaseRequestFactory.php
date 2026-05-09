<?php

namespace Database\Factories;

use App\Enums\DocumentPriority;
use App\Enums\PurchaseRequestStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseRequest>
 */
class PurchaseRequestFactory extends Factory
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
            'work_project_id' => Project::factory(),
            'requested_by' => User::factory(),
            'code' => strtoupper(fake()->bothify('SC-###')),
            'priority' => fake()->randomElement(DocumentPriority::values()),
            'request_date' => fake()->dateTimeBetween('-2 weeks', 'now'),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(PurchaseRequestStatus::values()),
        ];
    }
}
