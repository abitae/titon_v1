<?php

namespace App\Actions\Banks;

use App\Enums\BankMovementDirection;
use App\Enums\BankMovementType;
use App\Enums\CorrelativeSubject;
use App\Models\BankAccount;
use App\Models\BankMovement;
use App\Models\Company;
use App\Models\User;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RecordBankMovement
{
    /**
     * @param  array{
     *     type:string,
     *     amount:string|float,
     *     movement_date:string,
     *     concept:string,
     *     currency?:string|null,
     *     reference?:string|null,
     *     payment_method_id?:int|null,
     *     operation_type_id?:int|null,
     *     operation_number?:string|null,
     *     source?:Model|null
     * }  $payload
     */
    public function handle(BankAccount $bankAccount, User $actor, array $payload): BankMovement
    {
        return DB::transaction(function () use ($bankAccount, $actor, $payload): BankMovement {
            $bankAccount = BankAccount::query()->whereKey($bankAccount->id)->lockForUpdate()->firstOrFail();

            abort_unless($bankAccount->is_active, 422, 'La cuenta bancaria no está activa.');

            $movementType = $this->resolveType($payload['type']);
            $direction = $movementType->direction();
            $amount = round((float) $payload['amount'], 2);

            abort_if($amount <= 0, 422, 'El monto debe ser mayor a cero.');

            $currentBalance = round((float) $bankAccount->balance, 2);

            if ($direction === BankMovementDirection::Outbound && $amount > $currentBalance) {
                abort(422, 'Saldo insuficiente en la cuenta seleccionada.');
            }

            $balanceAfter = $direction === BankMovementDirection::Inbound
                ? round($currentBalance + $amount, 2)
                : round($currentBalance - $amount, 2);

            $bankAccount->update(['balance' => $balanceAfter]);

            $company = Company::query()->findOrFail((int) $bankAccount->company_id);
            $movementCode = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::BankMovement);

            $source = $payload['source'] ?? null;

            return BankMovement::query()->create([
                'company_id' => $bankAccount->company_id,
                'bank_account_id' => $bankAccount->id,
                'movement_code' => $movementCode,
                'direction' => $direction->value(),
                'type' => $movementType->value(),
                'amount' => $amount,
                'currency' => $payload['currency'] ?? $bankAccount->currency,
                'balance_after' => $balanceAfter,
                'movement_date' => $payload['movement_date'],
                'concept' => $payload['concept'],
                'reference' => $payload['reference'] ?? null,
                'payment_method_id' => $payload['payment_method_id'] ?? null,
                'operation_type_id' => $payload['operation_type_id'] ?? null,
                'operation_number' => $payload['operation_number'] ?? null,
                'source_type' => $source?->getMorphClass(),
                'source_id' => $source?->getKey(),
                'created_by_user_id' => $actor->id,
            ]);
        });
    }

    protected function resolveType(string $type): BankMovementType
    {
        foreach (BankMovementType::cases() as $movementType) {
            if ($movementType->value() === $type) {
                return $movementType;
            }
        }

        abort(422, 'Tipo de movimiento no válido.');
    }
}
