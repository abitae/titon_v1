<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrderItem>
 */
class PurchaseOrderItemFactory extends Factory
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
            'purchase_order_id' => PurchaseOrder::factory(),
            'product_or_service' => fake()->sentence(3),
            'unit' => fake()->randomElement(['und', 'm', 'm2', 'kg']),
            'quantity' => fake()->randomFloat(2, 1, 50),
            'unit_price' => fake()->randomFloat(2, 50, 500),
            'total' => fake()->randomFloat(2, 100, 5000),
        ];
    }
}
