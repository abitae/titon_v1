<?php

namespace Database\Factories;

use App\Enums\FleetSparePartStatus;
use App\Models\Company;
use App\Models\FleetSparePart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FleetSparePart>
 */
class FleetSparePartFactory extends Factory
{
    protected $model = FleetSparePart::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'supplier_id' => null,
            'warehouse_project_id' => null,
            'code' => 'RPT-'.fake()->unique()->numerify('####'),
            'name' => fake()->words(3, true),
            'category' => fake()->randomElement(['Motor', 'Frenos', 'Hidráulico']),
            'unit' => fake()->randomElement(['und', 'jgo', 'l']),
            'stock_quantity' => fake()->randomFloat(3, 1, 200),
            'min_stock' => 2,
            'unit_cost' => fake()->randomFloat(4, 5, 500),
            'status' => FleetSparePartStatus::Active->value(),
        ];
    }
}
