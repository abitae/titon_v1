<?php

namespace Database\Factories;

use App\Enums\WarehouseMovementDirection;
use App\Enums\WarehouseMovementSource;
use App\Models\Company;
use App\Models\User;
use App\Models\WarehouseMovement;
use App\Models\WarehouseStockItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseMovement>
 */
class WarehouseMovementFactory extends Factory
{
    protected $model = WarehouseMovement::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(3, 1, 50);
        $unitCost = fake()->randomFloat(4, 5, 200);

        return [
            'company_id' => Company::factory(),
            'warehouse_stock_item_id' => WarehouseStockItem::factory(),
            'warehouse_transfer_id' => null,
            'movement_code' => 'MOV-ALM-'.fake()->unique()->numerify('####'),
            'direction' => WarehouseMovementDirection::Inbound->value(),
            'source' => WarehouseMovementSource::OrderConformity->value(),
            'order_id' => null,
            'order_item_id' => null,
            'order_conformity_id' => null,
            'responsible_user_id' => User::factory(),
            'movement_date' => now()->toDateString(),
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_amount' => bcmul((string) $quantity, (string) $unitCost, 2),
            'reference' => null,
        ];
    }
}
