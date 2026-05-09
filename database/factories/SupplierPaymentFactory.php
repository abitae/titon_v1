<?php

namespace Database\Factories;

use App\Enums\CatalogType;
use App\Models\CatalogItem;
use App\Models\Company;
use App\Models\ContractPaymentSchedule;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\SupplierContract;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierPayment>
 */
class SupplierPaymentFactory extends Factory
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
            'supplier_contract_id' => SupplierContract::factory(),
            'supplier_id' => Supplier::factory(),
            'contract_payment_schedule_id' => ContractPaymentSchedule::factory(),
            'payment_date' => fake()->dateTimeBetween('-1 week', 'now'),
            'amount' => fake()->randomFloat(2, 200, 3000),
            'currency' => 'PEN',
            'operation_type_id' => CatalogItem::factory()->state(['type' => CatalogType::OperationType->value()]),
            'payment_method_id' => CatalogItem::factory()->state(['type' => CatalogType::PaymentMethod->value()]),
            'bank_id' => CatalogItem::factory()->state(['type' => CatalogType::Bank->value()]),
            'operation_number' => strtoupper(fake()->bothify('OP-###')),
            'responsible_user_id' => User::factory(),
            'concept' => fake()->sentence(4),
            'observation' => fake()->optional()->sentence(),
        ];
    }
}
