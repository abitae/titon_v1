<?php

namespace Database\Factories;

use App\Enums\CurrencyCode;
use App\Enums\PurchaseOrderStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
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
            'supplier_quotation_id' => SupplierQuotation::factory(),
            'code' => strtoupper(fake()->bothify('OC-###')),
            'issue_date' => fake()->dateTimeBetween('-1 week', 'now'),
            'currency' => fake()->randomElement(CurrencyCode::values()),
            'subtotal' => fake()->randomFloat(2, 1000, 10000),
            'tax' => fake()->randomFloat(2, 180, 1800),
            'total' => fake()->randomFloat(2, 1180, 11800),
            'status' => fake()->randomElement(PurchaseOrderStatus::values()),
            'conditions' => fake()->sentence(),
            'observation' => fake()->optional()->sentence(),
        ];
    }
}
