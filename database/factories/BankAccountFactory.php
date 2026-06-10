<?php

namespace Database\Factories;

use App\Enums\CatalogType;
use App\Models\BankAccount;
use App\Models\CatalogItem;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankAccount>
 */
class BankAccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'catalog_bank_id' => CatalogItem::factory()->state(['type' => CatalogType::Bank->value()]),
            'name' => 'Cuenta '.fake()->word(),
            'account_number' => fake()->numerify('##########'),
            'currency' => 'PEN',
            'balance' => fake()->randomFloat(2, 1000, 50000),
            'is_cash' => false,
            'is_active' => true,
        ];
    }

    public function cash(): static
    {
        return $this->state(fn (): array => [
            'catalog_bank_id' => null,
            'name' => 'Caja',
            'account_number' => null,
            'is_cash' => true,
        ]);
    }
}
