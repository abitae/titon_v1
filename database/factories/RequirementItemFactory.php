<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\RequirementItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RequirementItem>
 */
class RequirementItemFactory extends Factory
{
    protected $model = RequirementItem::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'work_project_id' => Project::factory(),
            'requirement_id' => Requirement::factory(),
            'item_type' => 'material',
            'description' => fake()->words(3, true),
            'unit' => 'und',
            'quantity' => fake()->randomFloat(2, 1, 50),
            'technical_specification' => fake()->optional()->sentence(),
            'observation' => fake()->optional()->sentence(),
        ];
    }
}
