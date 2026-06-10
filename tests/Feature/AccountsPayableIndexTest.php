<?php

use App\Livewire\AccountsPayable\ManageAccountsPayable;
use App\Models\AccountsPayable;
use App\Models\Company;
use App\Models\Order;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\PermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

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

    $this->project = Project::factory()->create(['company_id' => $this->company->id]);
    $this->supplier = Supplier::factory()->create(['company_id' => $this->company->id]);
});

test('accounts payable index lists records for active company', function () {
    $order = Order::factory()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'supplier_id' => $this->supplier->id,
        'total' => 1500,
    ]);

    $accountsPayable = AccountsPayable::factory()->create([
        'company_id' => $this->company->id,
        'order_id' => $order->id,
        'supplier_id' => $this->supplier->id,
        'work_project_id' => $this->project->id,
        'code' => 'CXP-TEST-001',
        'amount' => 1500,
        'balance' => 1500,
    ]);

    $this->get(route('accounts-payable.index'))
        ->assertSuccessful()
        ->assertSee('CXP-TEST-001');

    Livewire::test(ManageAccountsPayable::class)
        ->assertSee('CXP-TEST-001')
        ->assertSee($this->supplier->business_name);
});

test('accounts payable index hides records from other companies', function () {
    $otherCompany = Company::factory()->create();
    $otherProject = Project::factory()->create(['company_id' => $otherCompany->id]);
    $otherSupplier = Supplier::factory()->create(['company_id' => $otherCompany->id]);
    $otherOrder = Order::factory()->create([
        'company_id' => $otherCompany->id,
        'work_project_id' => $otherProject->id,
        'supplier_id' => $otherSupplier->id,
    ]);

    AccountsPayable::factory()->create([
        'company_id' => $otherCompany->id,
        'order_id' => $otherOrder->id,
        'supplier_id' => $otherSupplier->id,
        'work_project_id' => $otherProject->id,
        'code' => 'CXP-OTRA-EMPRESA',
    ]);

    Livewire::test(ManageAccountsPayable::class)
        ->assertDontSee('CXP-OTRA-EMPRESA')
        ->assertSee('No hay cuentas por pagar registradas');
});
