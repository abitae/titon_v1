<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
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
            'ruc' => fake()->unique()->numerify('20#########'),
            'business_name' => fake()->company(),
            'commercial_name' => fake()->companySuffix(),
            'contact_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'bank_name' => fake()->randomElement(['BCP', 'BBVA', 'Interbank']),
            'bank_account' => fake()->numerify('##########'),
            'cci' => fake()->numerify('####################'),
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }
}
