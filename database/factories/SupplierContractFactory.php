<?php

namespace Database\Factories;

use App\Enums\CurrencyCode;
use App\Enums\SupplierContractStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierContract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierContract>
 */
class SupplierContractFactory extends Factory
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
            'supplier_id' => Supplier::factory(),
            'order_id' => PurchaseOrder::factory(),
            'contract_number' => strtoupper(fake()->bothify('CT-###')),
            'contract_type' => fake()->randomElement(['Suministro', 'Servicio']),
            'start_date' => fake()->dateTimeBetween('-1 week', 'now'),
            'end_date' => fake()->dateTimeBetween('+1 month', '+6 months'),
            'total_amount' => fake()->randomFloat(2, 1000, 10000),
            'currency' => fake()->randomElement(CurrencyCode::values()),
            'payment_conditions' => fake()->sentence(),
            'penalties' => fake()->optional()->sentence(),
            'guarantees' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(SupplierContractStatus::values()),
            'observation' => fake()->optional()->sentence(),
        ];
    }
}
