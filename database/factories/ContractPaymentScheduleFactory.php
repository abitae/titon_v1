<?php

namespace Database\Factories;

use App\Enums\ContractPaymentScheduleStatus;
use App\Models\Company;
use App\Models\ContractPaymentSchedule;
use App\Models\SupplierContract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractPaymentSchedule>
 */
class ContractPaymentScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scheduledAmount = fake()->randomFloat(2, 1000, 10000);

        return [
            'company_id' => Company::factory(),
            'supplier_contract_id' => SupplierContract::factory(),
            'installment_number' => fake()->numberBetween(1, 6),
            'description' => fake()->sentence(3),
            'due_date' => fake()->dateTimeBetween('-1 week', '+1 month'),
            'scheduled_amount' => $scheduledAmount,
            'paid_amount' => 0,
            'balance' => $scheduledAmount,
            'status' => fake()->randomElement(ContractPaymentScheduleStatus::values()),
        ];
    }
}
