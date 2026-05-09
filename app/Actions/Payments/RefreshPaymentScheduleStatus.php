<?php

namespace App\Actions\Payments;

use App\Enums\ContractPaymentScheduleStatus;
use App\Models\ContractPaymentSchedule;

class RefreshPaymentScheduleStatus
{
    public function handle(ContractPaymentSchedule $schedule): ContractPaymentSchedule
    {
        $paidAmount = (float) $schedule->payments()->sum('amount');
        $scheduledAmount = (float) $schedule->scheduled_amount;
        $balance = max(0, $scheduledAmount - $paidAmount);

        $status = match (true) {
            $balance <= 0.0 => ContractPaymentScheduleStatus::Paid->value(),
            $paidAmount > 0.0 => ContractPaymentScheduleStatus::Partial->value(),
            $schedule->due_date->isPast() => ContractPaymentScheduleStatus::Expired->value(),
            default => ContractPaymentScheduleStatus::Pending->value(),
        };

        $schedule->update([
            'paid_amount' => $paidAmount,
            'balance' => $balance,
            'status' => $status,
        ]);

        return $schedule->refresh();
    }
}
