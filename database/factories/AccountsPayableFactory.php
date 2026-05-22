<?php

namespace Database\Factories;

use App\Enums\AccountsPayableStatus;
use App\Models\AccountsPayable;
use App\Models\Company;
use App\Models\Order;
use App\Models\Project;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountsPayable>
 */
class AccountsPayableFactory extends Factory
{
    protected $model = AccountsPayable::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 500, 50000);

        return [
            'company_id' => Company::factory(),
            'order_id' => Order::factory(),
            'supplier_id' => Supplier::factory(),
            'work_project_id' => Project::factory(),
            'code' => strtoupper(fake()->bothify('CXP-###')),
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'currency' => 'PEN',
            'amount' => $amount,
            'paid_amount' => 0,
            'balance' => $amount,
            'status' => AccountsPayableStatus::PendingDocuments->value(),
        ];
    }
}
