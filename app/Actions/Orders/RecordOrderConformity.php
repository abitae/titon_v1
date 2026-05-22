<?php

namespace App\Actions\Orders;

use App\Actions\AccountsPayable\CreateAccountsPayableFromOrder;
use App\Enums\ConformityResult;
use App\Enums\OrderStatus;
use App\Enums\RequirementStatus;
use App\Models\Order;
use App\Models\OrderConformity;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecordOrderConformity
{
    public function __construct(
        protected CreateAccountsPayableFromOrder $createAccountsPayable,
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

            return $conformity;
        });
    }
}
