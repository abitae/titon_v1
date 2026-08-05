<?php

use App\Livewire\Purchases\ManagePurchaseRequests;
use App\Models\Company;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use App\Services\Navigation\AppNavigation;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/**
 * @return array{user: User, company: Company, role: Role}
 */
function authenticateAsRole(string $roleName): array
{
    $company = Company::factory()->create();
    $user = User::factory()->create();
    $role = Role::findByName($roleName, 'web');

    $user->companies()->attach($company, [
        'role_id' => $role->id,
        'active' => true,
        'default_company' => true,
    ]);

    setPermissionsTeamId($company->id);
    $user->assignRole($role);

    test()->actingAs($user);
    session([CompanyContext::SESSION_KEY => $company->id]);

    return compact('user', 'company', 'role');
}

test('finanzas role can access banks module', function () {
    $this->seed(PermissionSeeder::class);
    authenticateAsRole('Finanzas');

    $this->get(route('modules.banks'))
        ->assertOk();
});

test('administrador role can delete companies', function () {
    $this->seed(PermissionSeeder::class);
    ['company' => $activeCompany] = authenticateAsRole('Administrador');

    $targetCompany = Company::factory()->create(['name' => 'Empresa eliminable']);

    $this->delete(route('companies.destroy', $targetCompany))
        ->assertRedirect(route('companies.index'));

    $this->assertDatabaseMissing('companies', [
        'id' => $targetCompany->id,
    ]);
});

test('compras role can delete purchase requests', function () {
    $this->seed(PermissionSeeder::class);
    ['user' => $user, 'company' => $company] = authenticateAsRole('Compras');

    $project = Project::factory()->create([
        'company_id' => $company->id,
        'responsible_user_id' => $user->id,
    ]);

    $purchaseRequest = PurchaseRequest::factory()->create([
        'company_id' => $company->id,
        'work_project_id' => $project->id,
        'responsible_user_id' => $user->id,
        'requested_by' => $user->id,
    ]);

    Livewire::test(ManagePurchaseRequests::class)
        ->call('deletePurchaseRequest', $purchaseRequest->id)
        ->assertHasNoErrors();

    $this->assertSoftDeleted('requirements', [
        'id' => $purchaseRequest->id,
    ]);
});

test('gerencia role sees procurement navigation items', function () {
    $this->seed(PermissionSeeder::class);
    authenticateAsRole('Gerencia');

    $comprasGroup = collect(app(AppNavigation::class)->sidebarGroups())
        ->firstWhere('heading', 'Compras');

    expect($comprasGroup)->not->toBeNull()
        ->and(collect($comprasGroup['items'])->pluck('label')->all())
        ->toContain('Requerimientos');
});

test('roles page shows edit action button for administrators', function () {
    $this->seed(PermissionSeeder::class);
    authenticateAsRole('Administrador');

    $this->get(route('security.roles'))
        ->assertOk()
        ->assertSee('aria-label="Editar"', false);
});

test('roles page hides edit action button for read-only role viewers', function () {
    $this->seed(PermissionSeeder::class);
    authenticateAsRole('Gerencia');

    $this->get(route('security.roles'))
        ->assertOk()
        ->assertDontSee('aria-label="Editar"', false)
        ->assertSee('Protegido', false);
});

test('consulta role can view banks module in read-only mode', function () {
    $this->seed(PermissionSeeder::class);
    authenticateAsRole('Consulta');

    $this->get(route('modules.banks'))
        ->assertOk();
});

test('finanzas role has expected bank permissions from matrix', function () {
    $this->seed(PermissionSeeder::class);

    $role = Role::findByName('Finanzas', 'web');

    expect($role->hasPermissionTo('bancos.ver'))->toBeTrue()
        ->and($role->hasPermissionTo('bancos.crear'))->toBeTrue()
        ->and($role->hasPermissionTo('bancos.editar'))->toBeTrue();
});
