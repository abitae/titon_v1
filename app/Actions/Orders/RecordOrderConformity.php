<?php

namespace App\Actions\Orders;

use App\Actions\AccountsPayable\CreateAccountsPayableFromOrder;
use App\Enums\ConformityResult;
use App\Enums\OrderStatus;
use App\Enums\RequirementStatus;
use App\Models\Order;
use App\Models\OrderConformity;
use App\Models\User;
use App\Services\Audit\UserAuditLogger;
use Illuminate\Support\Facades\DB;

class RecordOrderConformity
{
    public function __construct(
        protected CreateAccountsPayableFromOrder $createAccountsPayable,
        protected UserAuditLogger $userAuditLogger,
    ) {}

    public function handle(
        Order $order,
        User $responsible,
        string $result,
        ?string $observation = null,
        ?string $conformityDate = null,
    ): OrderConformity {
        return DB::transaction(function () use ($order, $responsible, $result, $observation, $conformityDate): OrderConformity {
            if ($result === ConformityResult::Rejected->value()) {
                abort_if(trim((string) $observation) === '', 422, 'La observación es obligatoria al rechazar.');
            }

            $existingConformity = OrderConformity::query()
                ->where('order_id', $order->id)
                ->first();

            $previousOrderStatus = $order->status;

            $conformity = OrderConformity::query()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'company_id' => $order->company_id,
                    'work_project_id' => $order->work_project_id,
                    'responsible_user_id' => $responsible->id,
                    'conformity_date' => $conformityDate ?? now()->toDateString(),
                    'result' => $result,
                    'observation' => $observation,
                ],
            );

            if ($result === ConformityResult::Conform->value()) {
                $order->update(['status' => OrderStatus::Conform->value()]);
                $this->createAccountsPayable->handle($order);

                $order->requirement?->update(['status' => RequirementStatus::Attended->value()]);
            } else {
                $order->update(['status' => OrderStatus::Rejected->value()]);
            }

            $order->refresh();

            $this->userAuditLogger->log(
                action: 'conformidad_registrada',
                module: 'Ordenes de compra',
                auditable: $order,
                oldValues: [
                    'order_code' => $order->code,
                    'order_status' => $previousOrderStatus,
                    'conformity_result' => $existingConformity?->result,
                    'conformity_date' => $existingConformity?->conformity_date?->toDateString(),
                ],
                newValues: [
                    'order_code' => $order->code,
                    'order_status' => $order->status,
                    'conformity_result' => $conformity->result,
                    'conformity_date' => $conformity->conformity_date?->toDateString(),
                    'conformity_id' => $conformity->id,
                ],
                observation: $observation,
                actor: $responsible,
                companyId: $order->company_id,
            );

            return $conformity;
        });
    }
}
