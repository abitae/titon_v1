<?php

namespace App\Actions\AccountsPayable;

use App\Enums\AccountsPayableStatus;
use App\Enums\CorrelativeSubject;
use App\Models\AccountsPayable;
use App\Models\Company;
use App\Models\Order;
use App\Services\Codes\CodeGeneratorService;
use Illuminate\Support\Facades\DB;

class CreateAccountsPayableFromOrder
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator,
        protected InitializePayableDocuments $initializeDocuments,
    ) {}

    public function handle(Order $order): AccountsPayable
    {
        return DB::transaction(function () use ($order): AccountsPayable {
            $existing = AccountsPayable::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $company = Company::query()->findOrFail($order->company_id);
            $project = $order->project()->firstOrFail();

            $accountsPayable = AccountsPayable::query()->create([
                'company_id' => $order->company_id,
                'order_id' => $order->id,
                'supplier_id' => $order->supplier_id,
                'work_project_id' => $order->work_project_id,
                'code' => $this->codeGenerator->generate($company, $project, CorrelativeSubject::AccountsPayable),
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'currency' => $order->currency,
                'amount' => $order->total,
                'paid_amount' => 0,
                'balance' => $order->total,
                'status' => AccountsPayableStatus::PendingDocuments->value(),
            ]);

            $this->initializeDocuments->handle($accountsPayable);

            return $accountsPayable;
        });
    }
}
