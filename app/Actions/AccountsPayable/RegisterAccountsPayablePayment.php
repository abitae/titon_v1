<?php

namespace App\Actions\AccountsPayable;

use App\Actions\Banks\RecordBankMovement;
use App\Enums\AccountsPayableStatus;
use App\Enums\BankMovementType;
use App\Enums\CatalogType;
use App\Models\AccountsPayable;
use App\Models\AccountsPayablePayment;
use App\Models\BankAccount;
use App\Models\CatalogItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterAccountsPayablePayment
{
    public function __construct(
        protected RecordBankMovement $recordBankMovement,
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

        $paymentMethod = CatalogItem::query()
            ->whereKey($data['payment_method_id'] ?? null)
            ->where('company_id', $accountsPayable->company_id)
            ->ofType(CatalogType::PaymentMethod)
            ->where('is_active', true)
            ->first();

        abort_unless($paymentMethod !== null, 422, 'Debe seleccionar un método de pago válido.');

        $bankAccount = BankAccount::query()
            ->whereKey($data['bank_account_id'] ?? null)
            ->where('company_id', $accountsPayable->company_id)
            ->where('is_active', true)
            ->first();

        abort_unless($bankAccount !== null, 422, 'Debe seleccionar una cuenta válida.');

        $payableCurrency = $data['currency'] ?? $accountsPayable->currency;

        abort_unless(
            $bankAccount->currency === $payableCurrency,
            422,
            'La moneda de la cuenta seleccionada no coincide con la CxP.',
        );

        if ($paymentMethod->requiresBankingDetails()) {
            abort_unless(
                filled($data['operation_type_id'] ?? null) && filled($data['operation_number'] ?? null),
                422,
                'Este método de pago requiere tipo de operación y número de operación.',
            );
        }

        if ($paymentMethod->requiresBankingDetails()) {
            abort_unless(! $bankAccount->is_cash, 422, 'Seleccione una cuenta bancaria para este método de pago.');
        } else {
            abort_unless($bankAccount->is_cash, 422, 'Los pagos en efectivo deben salir de la caja.');
        }

        return DB::transaction(function () use ($accountsPayable, $data, $payer, $amount, $paymentMethod, $bankAccount): AccountsPayablePayment {
            $requiresBankingDetails = $paymentMethod->requiresBankingDetails();

            $payment = AccountsPayablePayment::query()->create([
                'company_id' => $accountsPayable->company_id,
                'accounts_payable_id' => $accountsPayable->id,
                'supplier_id' => $accountsPayable->supplier_id,
                'work_project_id' => $accountsPayable->work_project_id,
                'payment_date' => $data['payment_date'],
                'amount' => $amount,
                'currency' => $data['currency'] ?? $accountsPayable->currency,
                'payment_method_id' => $paymentMethod->id,
                'bank_account_id' => $bankAccount->id,
                'bank_id' => $bankAccount->catalog_bank_id,
                'operation_type_id' => $requiresBankingDetails ? ($data['operation_type_id'] ?? null) : null,
                'operation_number' => $requiresBankingDetails ? ($data['operation_number'] ?? null) : null,
                'paid_by' => $payer->id,
                'concept' => $data['concept'],
                'observation' => $data['observation'] ?? null,
            ]);

            $this->recordBankMovement->handle($bankAccount, $payer, [
                'type' => BankMovementType::AccountsPayablePayment->value(),
                'amount' => $amount,
                'movement_date' => $data['payment_date'],
                'concept' => $data['concept'],
                'currency' => $data['currency'] ?? $accountsPayable->currency,
                'payment_method_id' => $paymentMethod->id,
                'operation_type_id' => $requiresBankingDetails ? ($data['operation_type_id'] ?? null) : null,
                'operation_number' => $requiresBankingDetails ? ($data['operation_number'] ?? null) : null,
                'reference' => $accountsPayable->code,
                'source' => $payment,
            ]);

            $paid = round((float) $accountsPayable->paid_amount + $amount, 2);
            $balance = round((float) $accountsPayable->amount - $paid, 2);

            $status = $balance <= 0
                ? AccountsPayableStatus::Paid->value()
                : AccountsPayableStatus::PartialPayment->value();

            $accountsPayable->update([
                'paid_amount' => $paid,
                'balance' => max(0, $balance),
                'status' => $balance <= 0 ? AccountsPayableStatus::Paid->value() : $status,
            ]);

            return $payment;
        });
    }
}
