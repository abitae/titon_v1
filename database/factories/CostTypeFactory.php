<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CostType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CostType>
 */
class CostTypeFactory extends Factory
{
    protected $model = CostType::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'code' => strtoupper($this->faker->bothify('TC-##')),
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 50),
        ];
    }
}
