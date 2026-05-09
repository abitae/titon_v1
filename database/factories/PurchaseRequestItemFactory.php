<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseRequestItem>
 */
class PurchaseRequestItemFactory extends Factory
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
            'purchase_request_id' => PurchaseRequest::factory(),
            'product_or_service' => fake()->sentence(3),
            'unit' => fake()->randomElement(['und', 'm', 'm2', 'kg']),
            'quantity' => fake()->randomFloat(2, 1, 50),
            'technical_specification' => fake()->sentence(),
            'observation' => fake()->optional()->sentence(),
        ];
    }
}
