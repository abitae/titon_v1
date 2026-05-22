<?php

namespace App\Actions\Contracts;

use App\Enums\CorrelativeSubject;
use App\Enums\OrderStatus;
use App\Enums\SupplierContractStatus;
use App\Models\Company;
use App\Models\Order;
use App\Models\SupplierContract;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;

class CreateSupplierContractFromOrder
{
    public function handle(Order $order): SupplierContract
    {
        $company = Company::query()->findOrFail($order->company_id);
        $existing = SupplierContract::query()
            ->where('order_id', $order->id)
            ->first();

        $contractNumber = $existing?->contract_number;

        if ($contractNumber === null || $contractNumber === '') {
            $contractNumber = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::SupplierContract);
        }

        $contract = SupplierContract::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'company_id' => $order->company_id,
                'work_project_id' => $order->work_project_id,
                'supplier_id' => $order->supplier_id,
                'contract_number' => $contractNumber,
                'contract_type' => 'Suministro',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'total_amount' => $order->total,
                'currency' => $order->currency,
                'payment_conditions' => $order->conditions,
                'penalties' => null,
                'guarantees' => null,
                'status' => SupplierContractStatus::Draft->value(),
                'observation' => $order->observation,
            ],
        );

        $order->update([
            'status' => OrderStatus::Attended->value(),
        ]);

        return $contract->refresh();
    }
}
