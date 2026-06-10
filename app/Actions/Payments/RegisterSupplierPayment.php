<?php

namespace App\Actions\Payments;

use App\Actions\Banks\RecordBankMovement;
use App\Enums\BankMovementType;
use App\Enums\CorrelativeSubject;
use App\Models\BankAccount;
use App\Models\Company;
use App\Models\SupplierPayment;
use App\Models\User;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use Illuminate\Support\Facades\DB;

class RegisterSupplierPayment
{
    public function __construct(
        protected RefreshPaymentScheduleStatus $refreshPaymentScheduleStatus,
        protected RecordBankMovement $recordBankMovement,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes, ?User $actor = null): SupplierPayment
    {
        return DB::transaction(function () use ($attributes, $actor): SupplierPayment {
            $company = Company::query()->findOrFail((int) $attributes['company_id']);

            if (! array_key_exists('registry_code', $attributes) || $attributes['registry_code'] === null || $attributes['registry_code'] === '') {
                $attributes['registry_code'] = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::SupplierPayment);
            }

            $bankAccount = null;

            if (filled($attributes['bank_account_id'] ?? null)) {
                $bankAccount = BankAccount::query()
                    ->whereKey($attributes['bank_account_id'])
                    ->where('company_id', $company->id)
                    ->where('is_active', true)
                    ->first();

                abort_unless($bankAccount !== null, 422, 'Debe seleccionar una cuenta válida.');

                if (filled($attributes['bank_id'] ?? null) === false) {
                    $attributes['bank_id'] = $bankAccount->catalog_bank_id;
                }
            }

            $payment = SupplierPayment::query()->create($attributes);

            if ($bankAccount !== null && $actor !== null) {
                $this->recordBankMovement->handle($bankAccount, $actor, [
                    'type' => BankMovementType::SupplierPayment->value(),
                    'amount' => $payment->amount,
                    'movement_date' => $payment->payment_date->toDateString(),
                    'concept' => $payment->concept,
                    'currency' => $payment->currency,
                    'payment_method_id' => $payment->payment_method_id,
                    'operation_type_id' => $payment->operation_type_id,
                    'operation_number' => $payment->operation_number,
                    'reference' => $payment->registry_code,
                    'source' => $payment,
                ]);
            }

            if ($payment->schedule !== null) {
                $this->refreshPaymentScheduleStatus->handle($payment->schedule);
            }

            return $payment->refresh();
        });
    }
}
