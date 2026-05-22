<?php

use App\Actions\AccountsPayable\InitializePayableDocuments;
use App\Actions\AccountsPayable\RegisterAccountsPayablePayment;
use App\Actions\Orders\RecordOrderConformity;
use App\Enums\AccountsPayableStatus;
use App\Enums\ConformityResult;
use App\Enums\OrderStatus;
use App\Models\AccountsPayable;
use App\Models\Company;
use App\Models\Order;
use App\Models\PayableDocument;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->company = Company::factory()->create();
    $this->user = User::factory()->create();
    $this->role = Role::findByName('Super Admin', 'web');

    $this->user->companies()->attach($this->company, [
        'role_id' => $this->role->id,
        'active' => true,
        'default_company' => true,
    ]);

    setPermissionsTeamId($this->company->id);
    $this->user->assignRole($this->role);
    $this->actingAs($this->user);
    session([CompanyContext::SESSION_KEY => $this->company->id]);

    $this->project = Project::factory()->create(['company_id' => $this->company->id, 'code' => 'OBR099']);
    $this->supplier = Supplier::factory()->create(['company_id' => $this->company->id]);
});

test('conformity on order creates accounts payable with required documents', function () {
    $order = Order::factory()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'supplier_id' => $this->supplier->id,
        'total' => 2500,
        'status' => OrderStatus::Attended->value(),
    ]);

    app(RecordOrderConformity::class)->handle(
        $order,
        $this->user,
        ConformityResult::Conform->value(),
        'Conforme en obra',
    );

    $accountsPayable = AccountsPayable::query()->where('order_id', $order->id)->first();

    expect($accountsPayable)->not->toBeNull();
    expect((float) $accountsPayable->amount)->toBe(2500.0);
    expect($accountsPayable->status)->toBe(AccountsPayableStatus::PendingDocuments->value());

    expect(PayableDocument::query()
        ->where('accounts_payable_id', $accountsPayable->id)
        ->where('document_type', 'factura')
        ->where('required', true)
        ->exists())->toBeTrue();
});

test('payment is blocked until required documents are uploaded', function () {
    $order = Order::factory()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'supplier_id' => $this->supplier->id,
        'total' => 1000,
    ]);

    $accountsPayable = AccountsPayable::factory()->create([
        'company_id' => $this->company->id,
        'order_id' => $order->id,
        'supplier_id' => $this->supplier->id,
        'work_project_id' => $this->project->id,
        'amount' => 1000,
        'balance' => 1000,
    ]);

    app(InitializePayableDocuments::class)->handle($accountsPayable);

    expect(fn () => app(RegisterAccountsPayablePayment::class)->handle(
        $accountsPayable,
        ['amount' => 1000, 'payment_date' => now()->toDateString(), 'concept' => 'Pago test'],
        $this->user,
    ))->toThrow(HttpException::class);
});
