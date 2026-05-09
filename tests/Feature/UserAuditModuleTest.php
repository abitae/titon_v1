<?php

use App\Livewire\Auditoria\Usuarios\ManageUserAudits;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

function makeAuditableUser(): array
{
    $user = User::factory()->create();
    $companyA = Company::factory()->create(['name' => 'Empresa Norte']);
    $companyB = Company::factory()->create(['name' => 'Empresa Sur']);
    $role = Role::findByName('Super Admin', 'web');

    $user->companies()->attach($companyA, [
        'role_id' => $role->id,
        'active' => true,
        'default_company' => true,
    ]);

    $user->companies()->attach($companyB, [
        'role_id' => $role->id,
        'active' => true,
        'default_company' => false,
    ]);

    setPermissionsTeamId($companyA->id);
    $user->assignRole($role);

    return [$user, $companyA, $companyB];
}

test('audit module renders and lists model audit entries', function () {
    [$user, $companyA] = makeAuditableUser();

    $this->actingAs($user);
    session([CompanyContext::SESSION_KEY => $companyA->id]);
    setPermissionsTeamId($companyA->id);

    $project = Project::factory()->create([
        'company_id' => $companyA->id,
        'responsible_user_id' => $user->id,
        'name' => 'Obra inicial',
    ]);

    $project->update(['name' => 'Obra auditada']);

    Livewire::test(ManageUserAudits::class)
        ->assertOk()
        ->assertSee('Auditoria de usuarios')
        ->assertSee('Obras')
        ->assertSee($user->name);
});

test('switching active company creates an audit record', function () {
    [$user, $companyA, $companyB] = makeAuditableUser();

    $this->actingAs($user);
    session([CompanyContext::SESSION_KEY => $companyA->id]);
    setPermissionsTeamId($companyA->id);

    $this->post(route('active-company.store'), [
        'company_id' => $companyB->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('audits', [
        'company_id' => $companyB->id,
        'user_id' => $user->id,
        'action' => 'cambio_empresa_activa',
        'module' => 'Seguridad',
    ]);
});

test('login and logout are recorded in audit trail', function () {
    [$user, $companyA] = makeAuditableUser();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertDatabaseHas('audits', [
        'user_id' => $user->id,
        'action' => 'inicio_sesion',
        'module' => 'Seguridad',
    ]);

    $this->actingAs($user);
    session([CompanyContext::SESSION_KEY => $companyA->id]);
    setPermissionsTeamId($companyA->id);

    $this->post(route('logout'))->assertRedirect(route('home'));

    $this->assertDatabaseHas('audits', [
        'user_id' => $user->id,
        'action' => 'cierre_sesion',
        'module' => 'Seguridad',
    ]);
});
