<?php

namespace Database\Factories;

use App\Enums\FleetSparePartMovementDirection;
use App\Models\FleetSparePart;
use App\Models\FleetSparePartMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FleetSparePartMovement>
 */
class FleetSparePartMovementFactory extends Factory
{
    protected $model = FleetSparePartMovement::class;

    public function definition(): array
    {
        $part = FleetSparePart::factory()->create();

        return [
            'company_id' => $part->company_id,
            'fleet_spare_part_id' => $part->id,
            'movement_code' => null,
            'fleet_work_order_id' => null,
            'created_by_user_id' => null,
            'direction' => FleetSparePartMovementDirection::Inbound->value(),
            'quantity' => 1,
            'unit_cost' => $part->unit_cost,
            'total_amount' => bcmul('1', (string) $part->unit_cost, 2),
            'reference' => fake()->optional()->bothify('REF-####'),
        ];
    }
}
