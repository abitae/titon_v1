<?php

namespace App\Actions\Warehouse;

use App\Enums\CorrelativeSubject;
use App\Enums\OrderType;
use App\Enums\WarehouseItemType;
use App\Enums\WarehouseMovementDirection;
use App\Enums\WarehouseMovementSource;
use App\Enums\WarehouseStockItemStatus;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderConformity;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\WarehouseMovement;
use App\Models\WarehouseStockItem;
use App\Services\Audit\UserAuditLogger;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use App\Services\Warehouse\RecalculateWarehouseStockItem;
use Illuminate\Support\Facades\DB;

class ReceiveOrderIntoWarehouse
{
    public function __construct(
        protected IssueCompanyCorrelativeCode $issueCompanyCorrelativeCode,
        protected RecalculateWarehouseStockItem $recalculateWarehouseStockItem,
        protected UserAuditLogger $userAuditLogger,
    ) {}

    public function handle(Order $order, OrderConformity $conformity, User $responsible): void
    {
        DB::transaction(function () use ($order, $conformity, $responsible): void {
            $order->loadMissing(['items', 'supplier']);

            $this->recalculateWarehouseStockItem->revertConformityMovementsForOrder((int) $order->id);

            $company = Company::query()->findOrFail((int) $order->company_id);
            $itemType = WarehouseItemType::fromOrderType(
                $order->order_type === OrderType::Service->value()
                    ? OrderType::Service
                    : OrderType::Purchase,
            );

            $affectedStockItemIds = [];

            foreach ($order->items as $orderItem) {
                $stockItem = $this->findOrCreateStockItem($order, $orderItem, $itemType);
                $quantity = (string) $orderItem->quantity;
                $unitCost = (string) $orderItem->unit_price;
                $totalAmount = bcmul($quantity, $unitCost, 2);

                $movementCode = $this->issueCompanyCorrelativeCode->issue(
                    $company,
                    CorrelativeSubject::WarehouseMovement,
                );

                WarehouseMovement::query()->updateOrCreate(
                    [
                        'order_item_id' => $orderItem->id,
                        'source' => WarehouseMovementSource::OrderConformity->value(),
                    ],
                    [
                        'company_id' => $order->company_id,
                        'warehouse_stock_item_id' => $stockItem->id,
                        'movement_code' => $movementCode,
                        'direction' => WarehouseMovementDirection::Inbound->value(),
                        'order_id' => $order->id,
                        'order_conformity_id' => $conformity->id,
                        'responsible_user_id' => $responsible->id,
                        'movement_date' => $conformity->conformity_date?->toDateString() ?? now()->toDateString(),
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'total_amount' => $totalAmount,
                        'reference' => 'Ingreso por conformidad '.$order->code,
                    ],
                );

                $affectedStockItemIds[] = $stockItem->id;
            }

            $this->recalculateWarehouseStockItem->handleMany($affectedStockItemIds);

            $this->userAuditLogger->log(
                action: 'almacen_ingreso_conformidad',
                module: 'Almacen',
                auditable: $order,
                oldValues: [],
                newValues: [
                    'order_code' => $order->code,
                    'conformity_id' => $conformity->id,
                    'items_count' => $order->items->count(),
                ],
                observation: null,
                actor: $responsible,
                companyId: (int) $order->company_id,
            );
        });
    }

    protected function findOrCreateStockItem(Order $order, OrderItem $orderItem, WarehouseItemType $itemType): WarehouseStockItem
    {
        return WarehouseStockItem::query()->firstOrCreate(
            [
                'company_id' => $order->company_id,
                'work_project_id' => $order->work_project_id,
                'item_type' => $itemType->value(),
                'description' => trim((string) $orderItem->description),
                'unit' => trim((string) $orderItem->unit),
            ],
            [
                'supplier_id' => $order->supplier_id,
                'stock_quantity' => 0,
                'unit_cost' => $orderItem->unit_price,
                'status' => WarehouseStockItemStatus::Active->value(),
            ],
        );
    }
}
