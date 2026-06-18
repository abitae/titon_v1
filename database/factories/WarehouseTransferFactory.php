<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Models\WarehouseStockItem;
use App\Models\WarehouseTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseTransfer>
 */
class WarehouseTransferFactory extends Factory
{
    protected $model = WarehouseTransfer::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(3, 1, 20);
        $unitCost = fake()->randomFloat(4, 5, 200);

        return [
            'company_id' => Company::factory(),
            'transfer_code' => 'TRF-ALM-'.fake()->unique()->numerify('####'),
            'source_work_project_id' => Project::factory(),
            'destination_work_project_id' => Project::factory(),
            'warehouse_stock_item_id' => WarehouseStockItem::factory(),
            'responsible_user_id' => User::factory(),
            'transfer_date' => now()->toDateString(),
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_amount' => bcmul((string) $quantity, (string) $unitCost, 2),
            'reference' => fake()->optional()->sentence(),
        ];
    }
}
