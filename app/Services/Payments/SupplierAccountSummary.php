<?php

namespace App\Services\Payments;

use App\Models\SupplierContract;
use App\Models\SupplierPayment;
use Illuminate\Support\Collection;

class SupplierAccountSummary
{
    /**
     * @return array<string, mixed>
     */
    public function byContract(SupplierContract $supplierContract): array
    {
        $payments = $supplierContract->payments()->with(['schedule', 'operationType', 'responsibleUser'])->latest('payment_date')->get();

        return [
            'payments' => $payments,
            'total_paid' => (float) $payments->sum(fn (SupplierPayment $payment): float => (float) $payment->amount),
            'pending_balance' => $supplierContract->pendingBalance(),
            'schedule_total' => (float) $supplierContract->paymentSchedules()->sum('scheduled_amount'),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function bySupplier(int $supplierId): Collection
    {
        return SupplierContract::query()
            ->where('supplier_id', $supplierId)
            ->with(['project', 'payments'])
            ->get()
            ->map(function (SupplierContract $contract): array {
                return [
                    'contract' => $contract,
                    'total_paid' => $contract->totalPaid(),
                    'pending_balance' => $contract->pendingBalance(),
                ];
            });
    }
}
