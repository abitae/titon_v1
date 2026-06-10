<?php

use App\Enums\BankMovementType;
use App\Enums\CatalogType;
use App\Livewire\Banks\ManageBanks;
use App\Models\BankAccount;
use App\Models\BankMovement;
use App\Models\CatalogItem;
use App\Models\Company;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\PermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->company = Company::factory()->create();
    $this->seed(CatalogSeeder::class);
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

    $this->institution = CatalogItem::query()
        ->where('company_id', $this->company->id)
        ->where('type', CatalogType::Bank->value())
        ->firstOrFail();
});

test('bank account can be created with opening balance movement', function () {
    Livewire::test(ManageBanks::class)
        ->call('openCreateAccountModal')
        ->set('account_name', 'Cuenta operativa')
        ->set('catalog_bank_id', $this->institution->id)
        ->set('account_number', '19100123456')
        ->set('opening_balance', '2500')
        ->call('saveAccount')
        ->assertHasNoErrors();

    $account = BankAccount::query()->where('company_id', $this->company->id)->where('name', 'Cuenta operativa')->first();

    expect($account)->not->toBeNull();
    expect((float) $account->balance)->toBe(2500.0);
    expect($account->movements()->where('type', BankMovementType::Deposit->value())->count())->toBe(1);
});

test('withdrawal reduces account balance', function () {
    $account = BankAccount::factory()->create([
        'company_id' => $this->company->id,
        'catalog_bank_id' => $this->institution->id,
        'balance' => 1000,
    ]);

    Livewire::test(ManageBanks::class)
        ->call('openMovementModal', $account->id, 'withdrawal')
        ->set('movement_bank_account_id', $account->id)
        ->set('movement_amount', '250')
        ->set('movement_date', now()->toDateString())
        ->set('movement_concept', 'Retiro caja chica')
        ->call('saveMovement')
        ->assertHasNoErrors();

    expect((float) $account->fresh()->balance)->toBe(750.0);
    expect(BankMovement::query()->where('bank_account_id', $account->id)->where('type', BankMovementType::Withdrawal->value())->exists())->toBeTrue();
});

test('withdrawal is blocked when balance is insufficient', function () {
    $account = BankAccount::factory()->create([
        'company_id' => $this->company->id,
        'balance' => 100,
    ]);

    Livewire::test(ManageBanks::class)
        ->call('openMovementModal', $account->id, 'withdrawal')
        ->set('movement_bank_account_id', $account->id)
        ->set('movement_amount', '500')
        ->set('movement_date', now()->toDateString())
        ->set('movement_concept', 'Retiro excesivo')
        ->call('saveMovement');

    expect((float) $account->fresh()->balance)->toBe(100.0);
});
