<?php

namespace App\Actions\Mechanics;

use App\Enums\CorrelativeSubject;
use App\Enums\FleetSparePartMovementDirection;
use App\Models\Company;
use App\Models\FleetSparePart;
use App\Models\FleetSparePartMovement;
use App\Models\FleetWorkOrder;
use App\Models\User;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use Illuminate\Support\Facades\DB;

class RecordFleetSparePartMovement
{
    /**
     * @param  array{
     *     direction:string,
     *     quantity:string|float,
     *     unit_cost?:string|float|null,
     *     fleet_work_order_id?:int|null,
     *     reference?:string|null
     * }  $payload
     */
    public function handle(FleetSparePart $sparePart, User $actor, array $payload): FleetSparePartMovement
    {
        return DB::transaction(function () use ($sparePart, $actor, $payload): FleetSparePartMovement {
            $direction = $payload['direction'];
            $quantity = (string) $payload['quantity'];
            $unitCost = isset($payload['unit_cost']) && $payload['unit_cost'] !== null && $payload['unit_cost'] !== ''
                ? (string) $payload['unit_cost']
                : (string) $sparePart->unit_cost;

            $totalAmount = bcmul($quantity, $unitCost, 2);

            if ($direction === FleetSparePartMovementDirection::Outbound->value()) {
                $workOrderId = $payload['fleet_work_order_id'] ?? null;
                abort_if($workOrderId === null, 422, 'La salida debe asociarse a una orden de trabajo.');

                $workOrder = FleetWorkOrder::query()->findOrFail($workOrderId);
                abort_unless((int) $workOrder->company_id === (int) $sparePart->company_id, 422, 'La OT no pertenece a la misma empresa que el repuesto.');

                if (bccomp((string) $sparePart->stock_quantity, $quantity, 3) < 0) {
                    abort(422, 'Stock insuficiente.');
                }

                $sparePart->update([
                    'stock_quantity' => bcsub((string) $sparePart->stock_quantity, $quantity, 3),
                ]);

                $workOrder->increment('spare_parts_cost', (float) $totalAmount);
                $workOrder->refresh();
                $workOrder->save();
            } else {
                $sparePart->update([
                    'stock_quantity' => bcadd((string) $sparePart->stock_quantity, $quantity, 3),
                ]);
            }

            $company = Company::query()->findOrFail((int) $sparePart->company_id);
            $movementCode = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::FleetSparePartMovement);

            return FleetSparePartMovement::query()->create([
                'company_id' => $sparePart->company_id,
                'fleet_spare_part_id' => $sparePart->id,
                'movement_code' => $movementCode,
                'fleet_work_order_id' => $payload['fleet_work_order_id'] ?? null,
                'created_by_user_id' => $actor->id,
                'direction' => $direction,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_amount' => $totalAmount,
                'reference' => $payload['reference'] ?? null,
            ]);
        });
    }
}
