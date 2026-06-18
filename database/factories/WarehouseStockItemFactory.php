<?php

namespace Database\Factories;

use App\Enums\WarehouseItemType;
use App\Enums\WarehouseStockItemStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\WarehouseStockItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseStockItem>
 */
class WarehouseStockItemFactory extends Factory
{
    protected $model = WarehouseStockItem::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'work_project_id' => Project::factory(),
            'supplier_id' => Supplier::factory(),
            'item_type' => WarehouseItemType::Material->value(),
            'description' => fake()->words(3, true),
            'unit' => fake()->randomElement(['und', 'm', 'kg']),
            'stock_quantity' => fake()->randomFloat(3, 1, 100),
            'unit_cost' => fake()->randomFloat(4, 5, 500),
            'status' => WarehouseStockItemStatus::Active->value(),
        ];
    }

    public function service(): static
    {
        return $this->state(fn (): array => [
            'item_type' => WarehouseItemType::Service->value(),
        ]);
    }
}
