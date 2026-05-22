<?php

namespace App\Actions\AccountsPayable;

use App\Enums\AccountsPayableStatus;
use App\Models\AccountsPayable;
use App\Models\AccountsPayablePayment;
use App\Models\Company;
use App\Models\User;
use App\Services\Codes\CodeGeneratorService;
use Illuminate\Support\Facades\DB;

class RegisterAccountsPayablePayment
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(AccountsPayable $accountsPayable, array $data, User $payer): AccountsPayablePayment
    {
        abort_unless($accountsPayable->requiredDocumentsUploaded(), 422, 'Faltan documentos obligatorios.');

        $amount = (float) $data['amount'];

        abort_if($amount <= 0, 422);
        abort_if($amount > (float) $accountsPayable->balance, 422, 'El pago supera el saldo pendiente.');

        return DB::transaction(function () use ($accountsPayable, $data, $payer, $amount): AccountsPayablePayment {
            $company = Company::query()->findOrFail($accountsPayable->company_id);
            $project = $accountsPayable->project()->firstOrFail();

            $payment = AccountsPayablePayment::query()->create([
                'company_id' => $accountsPayable->company_id,
                'accounts_payable_id' => $accountsPayable->id,
                'supplier_id' => $accountsPayable->supplier_id,
                'work_project_id' => $accountsPayable->work_project_id,
                'payment_date' => $data['payment_date'],
                'amount' => $amount,
                'currency' => $data['currency'] ?? $accountsPayable->currency,
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'bank_id' => $data['bank_id'] ?? null,
                'operation_type_id' => $data['operation_type_id'] ?? null,
                'operation_number' => $data['operation_number'] ?? null,
                'paid_by' => $payer->id,
                'concept' => $data['concept'],
                'observation' => $data['observation'] ?? null,
            ]);

            $paid = round((float) $accountsPayable->paid_amount + $amount, 2);
            $balance = round((float) $accountsPayable->amount - $paid, 2);

            $status = $balance <= 0
                ? AccountsPayableStatus::Paid->value()
                : AccountsPayableStatus::PartialPayment->value();

            if ($balance > 0 && $accountsPayable->requiredDocumentsUploaded()) {
                $status = AccountsPayableStatus::PartialPayment->value();
            }

            $accountsPayable->update([
                'paid_amount' => $paid,
                'balance' => max(0, $balance),
                'status' => $balance <= 0 ? AccountsPayableStatus::Paid->value() : $status,
            ]);

            if ($balance <= 0) {
                $accountsPayable->update(['status' => AccountsPayableStatus::Paid->value()]);
            } elseif ($accountsPayable->requiredDocumentsUploaded()) {
                $accountsPayable->update(['status' => AccountsPayableStatus::PartialPayment->value()]);
            }

            return $payment;
        });
    }
}
