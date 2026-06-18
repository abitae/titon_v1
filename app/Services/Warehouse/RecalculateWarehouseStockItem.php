<?php

namespace App\Services\Warehouse;

use App\Enums\WarehouseMovementDirection;
use App\Models\WarehouseMovement;
use App\Models\WarehouseStockItem;

class RecalculateWarehouseStockItem
{
    public function handle(WarehouseStockItem $stockItem): WarehouseStockItem
    {
        $stockItem->loadMissing('movements');

        $inbound = '0';
        $outbound = '0';
        $inboundValue = '0';
        $inboundQty = '0';

        foreach ($stockItem->movements as $movement) {
            $quantity = (string) $movement->quantity;

            if ($movement->direction === WarehouseMovementDirection::Inbound->value()) {
                $inbound = bcadd($inbound, $quantity, 3);
                $inboundValue = bcadd($inboundValue, (string) $movement->total_amount, 4);
                $inboundQty = bcadd($inboundQty, $quantity, 3);
            } else {
                $outbound = bcadd($outbound, $quantity, 3);
            }
        }

        $stockQuantity = bcsub($inbound, $outbound, 3);

        $unitCost = bccomp($inboundQty, '0', 3) > 0
            ? bcdiv($inboundValue, $inboundQty, 4)
            : (string) $stockItem->unit_cost;

        $stockItem->update([
            'stock_quantity' => $stockQuantity,
            'unit_cost' => $unitCost,
        ]);

        return $stockItem->refresh();
    }

    /**
     * @param  array<int, int>  $stockItemIds
     */
    public function handleMany(array $stockItemIds): void
    {
        foreach (array_unique($stockItemIds) as $stockItemId) {
            $stockItem = WarehouseStockItem::query()->find($stockItemId);

            if ($stockItem !== null) {
                $this->handle($stockItem);
            }
        }
    }

    public function revertConformityMovementsForOrder(int $orderId): void
    {
        $movements = WarehouseMovement::query()
            ->where('order_id', $orderId)
            ->where('source', 'conformidad_orden')
            ->get();

        if ($movements->isEmpty()) {
            return;
        }

        $stockItemIds = $movements->pluck('warehouse_stock_item_id')->all();

        WarehouseMovement::query()
            ->whereIn('id', $movements->pluck('id'))
            ->delete();

        $this->handleMany($stockItemIds);
    }
}
