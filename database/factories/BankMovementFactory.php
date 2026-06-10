<?php

namespace Database\Factories;

use App\Enums\BankMovementDirection;
use App\Enums\BankMovementType;
use App\Models\BankAccount;
use App\Models\BankMovement;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankMovement>
 */
class BankMovementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 100, 5000);

        return [
            'company_id' => Company::factory(),
            'bank_account_id' => BankAccount::factory(),
            'movement_code' => 'MOV-'.fake()->unique()->numerify('######'),
            'direction' => BankMovementDirection::Inbound->value(),
            'type' => BankMovementType::Deposit->value(),
            'amount' => $amount,
            'currency' => 'PEN',
            'balance_after' => $amount,
            'movement_date' => now()->toDateString(),
            'concept' => fake()->sentence(3),
            'reference' => null,
            'created_by_user_id' => User::factory(),
        ];
    }
}
