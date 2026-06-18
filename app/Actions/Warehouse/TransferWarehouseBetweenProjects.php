<?php

namespace App\Actions\Warehouse;

use App\Enums\CorrelativeSubject;
use App\Enums\WarehouseItemType;
use App\Enums\WarehouseMovementDirection;
use App\Enums\WarehouseMovementSource;
use App\Enums\WarehouseStockItemStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Models\WarehouseMovement;
use App\Models\WarehouseStockItem;
use App\Models\WarehouseTransfer;
use App\Services\Audit\UserAuditLogger;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use App\Services\Warehouse\RecalculateWarehouseStockItem;
use Illuminate\Support\Facades\DB;

class TransferWarehouseBetweenProjects
{
    public function __construct(
        protected IssueCompanyCorrelativeCode $issueCompanyCorrelativeCode,
        protected RecalculateWarehouseStockItem $recalculateWarehouseStockItem,
        protected UserAuditLogger $userAuditLogger,
    ) {}

    /**
     * @param  array{
     *     destination_work_project_id:int,
     *     quantity:string|float,
     *     transfer_date?:string|null,
     *     reference?:string|null
     * }  $payload
     */
    public function handle(WarehouseStockItem $sourceItem, User $actor, array $payload): WarehouseTransfer
    {
        return DB::transaction(function () use ($sourceItem, $actor, $payload): WarehouseTransfer {
            abort_unless($sourceItem->item_type === WarehouseItemType::Material->value(), 422, 'Solo se pueden transferir materiales.');

            $destinationProjectId = (int) $payload['destination_work_project_id'];
            abort_if($destinationProjectId === (int) $sourceItem->work_project_id, 422, 'La obra destino debe ser distinta a la origen.');

            $destinationProject = Project::query()->findOrFail($destinationProjectId);
            abort_unless((int) $destinationProject->company_id === (int) $sourceItem->company_id, 422, 'La obra destino no pertenece a la misma empresa.');

            $quantity = (string) $payload['quantity'];
            abort_if(bccomp($quantity, '0', 3) <= 0, 422, 'La cantidad debe ser mayor a cero.');

            $sourceItem->refresh();

            if (! $sourceItem->hasAvailableStock($quantity)) {
                abort(422, 'Stock insuficiente para transferir.');
            }

            $unitCost = (string) $sourceItem->unit_cost;
            $totalAmount = bcmul($quantity, $unitCost, 2);
            $company = Company::query()->findOrFail((int) $sourceItem->company_id);
            $transferDate = $payload['transfer_date'] ?? now()->toDateString();

            $transferCode = $this->issueCompanyCorrelativeCode->issue(
                $company,
                CorrelativeSubject::WarehouseTransfer,
            );

            $transfer = WarehouseTransfer::query()->create([
                'company_id' => $sourceItem->company_id,
                'transfer_code' => $transferCode,
                'source_work_project_id' => $sourceItem->work_project_id,
                'destination_work_project_id' => $destinationProjectId,
                'warehouse_stock_item_id' => $sourceItem->id,
                'responsible_user_id' => $actor->id,
                'transfer_date' => $transferDate,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_amount' => $totalAmount,
                'reference' => $payload['reference'] ?? null,
            ]);

            $outboundCode = $this->issueCompanyCorrelativeCode->issue(
                $company,
                CorrelativeSubject::WarehouseMovement,
            );

            WarehouseMovement::query()->create([
                'company_id' => $sourceItem->company_id,
                'warehouse_stock_item_id' => $sourceItem->id,
                'warehouse_transfer_id' => $transfer->id,
                'movement_code' => $outboundCode,
                'direction' => WarehouseMovementDirection::Outbound->value(),
                'source' => WarehouseMovementSource::TransferOutbound->value(),
                'responsible_user_id' => $actor->id,
                'movement_date' => $transferDate,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_amount' => $totalAmount,
                'reference' => $payload['reference'] ?? 'Transferencia a obra '.$destinationProject->code,
            ]);

            $destinationItem = WarehouseStockItem::query()->firstOrCreate(
                [
                    'company_id' => $sourceItem->company_id,
                    'work_project_id' => $destinationProjectId,
                    'item_type' => WarehouseItemType::Material->value(),
                    'description' => $sourceItem->description,
                    'unit' => $sourceItem->unit,
                ],
                [
                    'supplier_id' => $sourceItem->supplier_id,
                    'stock_quantity' => 0,
                    'unit_cost' => $unitCost,
                    'status' => WarehouseStockItemStatus::Active->value(),
                ],
            );

            $inboundCode = $this->issueCompanyCorrelativeCode->issue(
                $company,
                CorrelativeSubject::WarehouseMovement,
            );

            WarehouseMovement::query()->create([
                'company_id' => $sourceItem->company_id,
                'warehouse_stock_item_id' => $destinationItem->id,
                'warehouse_transfer_id' => $transfer->id,
                'movement_code' => $inboundCode,
                'direction' => WarehouseMovementDirection::Inbound->value(),
                'source' => WarehouseMovementSource::TransferInbound->value(),
                'responsible_user_id' => $actor->id,
                'movement_date' => $transferDate,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_amount' => $totalAmount,
                'reference' => $payload['reference'] ?? 'Transferencia desde obra '.$sourceItem->project?->code,
            ]);

            $this->recalculateWarehouseStockItem->handleMany([
                $sourceItem->id,
                $destinationItem->id,
            ]);

            $this->userAuditLogger->log(
                action: 'almacen_transferencia',
                module: 'Almacen',
                auditable: $transfer,
                oldValues: [],
                newValues: [
                    'transfer_code' => $transfer->transfer_code,
                    'quantity' => $quantity,
                    'source_project_id' => $sourceItem->work_project_id,
                    'destination_project_id' => $destinationProjectId,
                ],
                observation: $payload['reference'] ?? null,
                actor: $actor,
                companyId: (int) $sourceItem->company_id,
            );

            return $transfer->load(['sourceProject', 'destinationProject', 'movements']);
        });
    }
}
