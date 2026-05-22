<?php

namespace Database\Factories;

use App\Enums\DocumentPriority;
use App\Enums\RequirementStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Requirement>
 */
class RequirementFactory extends Factory
{
    protected $model = Requirement::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'work_project_id' => Project::factory(),
            'responsible_user_id' => User::factory(),
            'requested_by' => User::factory(),
            'code' => strtoupper(fake()->bothify('REQ-###')),
            'title' => fake()->sentence(4),
            'requirement_type' => fake()->randomElement(['material', 'servicio', 'producto']),
            'priority' => fake()->randomElement(DocumentPriority::values()),
            'request_date' => fake()->dateTimeBetween('-2 weeks', 'now'),
            'needed_date' => fake()->dateTimeBetween('now', '+1 month'),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(RequirementStatus::values()),
        ];
    }
}
