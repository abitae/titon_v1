<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $qty = fake()->randomFloat(2, 1, 20);
        $price = fake()->randomFloat(2, 10, 500);

        return [
            'company_id' => Company::factory(),
            'work_project_id' => Project::factory(),
            'order_id' => Order::factory(),
            'description' => fake()->words(3, true),
            'unit' => 'und',
            'quantity' => $qty,
            'unit_price' => $price,
            'total' => round($qty * $price, 2),
        ];
    }
}
