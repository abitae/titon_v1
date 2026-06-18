<?php

namespace App\Actions\Warehouse;

use App\Enums\CorrelativeSubject;
use App\Enums\WarehouseItemType;
use App\Enums\WarehouseMovementDirection;
use App\Enums\WarehouseMovementSource;
use App\Models\Company;
use App\Models\User;
use App\Models\WarehouseMovement;
use App\Models\WarehouseStockItem;
use App\Services\Audit\UserAuditLogger;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use App\Services\Warehouse\RecalculateWarehouseStockItem;
use Illuminate\Support\Facades\DB;

class RecordWarehouseOutbound
{
    public function __construct(
        protected IssueCompanyCorrelativeCode $issueCompanyCorrelativeCode,
        protected RecalculateWarehouseStockItem $recalculateWarehouseStockItem,
        protected UserAuditLogger $userAuditLogger,
    ) {}

    /**
     * @param  array{
     *     quantity:string|float,
     *     movement_date?:string|null,
     *     reference?:string|null
     * }  $payload
     */
    public function handle(WarehouseStockItem $stockItem, User $actor, array $payload): WarehouseMovement
    {
        return DB::transaction(function () use ($stockItem, $actor, $payload): WarehouseMovement {
            abort_unless($stockItem->item_type === WarehouseItemType::Material->value(), 422, 'Solo se pueden registrar salidas de materiales.');

            $quantity = (string) $payload['quantity'];
            abort_if(bccomp($quantity, '0', 3) <= 0, 422, 'La cantidad debe ser mayor a cero.');

            $stockItem->refresh();

            if (! $stockItem->hasAvailableStock($quantity)) {
                abort(422, 'Stock insuficiente.');
            }

            $unitCost = (string) $stockItem->unit_cost;
            $totalAmount = bcmul($quantity, $unitCost, 2);
            $company = Company::query()->findOrFail((int) $stockItem->company_id);

            $movementCode = $this->issueCompanyCorrelativeCode->issue(
                $company,
                CorrelativeSubject::WarehouseMovement,
            );

            $movement = WarehouseMovement::query()->create([
                'company_id' => $stockItem->company_id,
                'warehouse_stock_item_id' => $stockItem->id,
                'movement_code' => $movementCode,
                'direction' => WarehouseMovementDirection::Outbound->value(),
                'source' => WarehouseMovementSource::ManualOutbound->value(),
                'responsible_user_id' => $actor->id,
                'movement_date' => $payload['movement_date'] ?? now()->toDateString(),
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_amount' => $totalAmount,
                'reference' => $payload['reference'] ?? null,
            ]);

            $this->recalculateWarehouseStockItem->handle($stockItem);

            $this->userAuditLogger->log(
                action: 'almacen_salida_manual',
                module: 'Almacen',
                auditable: $stockItem,
                oldValues: [],
                newValues: [
                    'movement_code' => $movement->movement_code,
                    'quantity' => $quantity,
                    'stock_item_description' => $stockItem->description,
                ],
                observation: $payload['reference'] ?? null,
                actor: $actor,
                companyId: (int) $stockItem->company_id,
            );

            return $movement;
        });
    }
}
