<?php

namespace Database\Factories;

use App\Enums\CurrencyCode;
use App\Enums\QuotationStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierQuotation>
 */
class SupplierQuotationFactory extends Factory
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
            'requirement_id' => Requirement::factory(),
            'supplier_id' => Supplier::factory(),
            'code' => strtoupper(fake()->bothify('COT-###')),
            'quotation_date' => fake()->dateTimeBetween('-5 days', 'now'),
            'valid_until' => fake()->dateTimeBetween('now', '+2 weeks'),
            'currency' => fake()->randomElement(CurrencyCode::values()),
            'subtotal' => fake()->randomFloat(2, 1000, 10000),
            'tax' => fake()->randomFloat(2, 180, 1800),
            'total' => fake()->randomFloat(2, 1180, 11800),
            'delivery_time_days' => fake()->numberBetween(1, 15),
            'payment_conditions' => fake()->sentence(),
            'warranty' => fake()->sentence(),
            'status' => QuotationStatus::Registered->value(),
            'observation' => fake()->optional()->sentence(),
        ];
    }
}
