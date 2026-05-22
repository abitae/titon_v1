<?php

namespace Database\Factories;

use App\Enums\CurrencyCode;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Company;
use App\Models\Order;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'work_project_id' => Project::factory(),
            'requirement_id' => Requirement::factory(),
            'supplier_id' => Supplier::factory(),
            'supplier_quotation_id' => SupplierQuotation::factory(),
            'code' => strtoupper(fake()->bothify('OC-###')),
            'order_type' => OrderType::Purchase->value(),
            'issue_date' => fake()->dateTimeBetween('-1 week', 'now'),
            'currency' => fake()->randomElement(CurrencyCode::values()),
            'subtotal' => fake()->randomFloat(2, 1000, 10000),
            'tax' => fake()->randomFloat(2, 180, 1800),
            'total' => fake()->randomFloat(2, 1180, 11800),
            'status' => OrderStatus::Issued->value(),
        ];
    }
}
