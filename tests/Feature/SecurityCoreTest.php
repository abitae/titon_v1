<?php

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('user can switch active company without logging out and permissions change with the company context', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create();
    $companyA = Company::factory()->create(['name' => 'Empresa A']);
    $companyB = Company::factory()->create(['name' => 'Empresa B']);
    $adminRole = Role::findByName('Administrador', 'web');
    $viewerRole = Role::findByName('Consulta', 'web');

    $user->companies()->attach($companyA, [
        'role_id' => $adminRole->id,
        'active' => true,
        'default_company' => true,
    ]);

    $user->companies()->attach($companyB, [
        'role_id' => $viewerRole->id,
        'active' => true,
        'default_company' => false,
    ]);

    setPermissionsTeamId($companyA->id);
    $user->assignRole($adminRole);

    setPermissionsTeamId($companyB->id);
    $user->assignRole($viewerRole);

    $this->actingAs($user);

    $this->get(route('users.create'))->assertOk();

    $this->post(route('active-company.store'), [
        'company_id' => $companyB->id,
    ])->assertRedirect();

    $this->assertAuthenticatedAs($user);
    $this->assertSame($companyB->id, session('active_company_id'));

    $this->get(route('users.create'))->assertForbidden();
});

test('companies index shows edit action for users with edit permission', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create();
    $company = Company::factory()->create();
    $role = Role::findByName('Administrador', 'web');

    $user->companies()->attach($company, [
        'role_id' => $role->id,
        'active' => true,
        'default_company' => true,
    ]);

    setPermissionsTeamId($company->id);
    $user->assignRole($role);

    $targetCompany = Company::factory()->create(['name' => 'Empresa editable']);

    $this->actingAs($user)
        ->get(route('companies.index'))
        ->assertOk()
        ->assertSee(route('companies.edit', $targetCompany), false)
        ->assertSee('aria-label="Editar"', false);
});

test('authorized users can create companies through the admin module', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create();
    $company = Company::factory()->create();
    $role = Role::findByName('Administrador', 'web');

    $user->companies()->attach($company, [
        'role_id' => $role->id,
        'active' => true,
        'default_company' => true,
    ]);

    setPermissionsTeamId($company->id);
    $user->assignRole($role);

    $this->actingAs($user);

    $this->post(route('companies.store'), [
        'name' => 'Nueva Empresa',
        'business_name' => 'Nueva Empresa S.A.C.',
        'ruc' => '20999999999',
        'address' => 'Av. Principal 123',
        'phone' => '012345678',
        'email' => 'nueva@empresa.pe',
        'logo' => null,
        'primary_color' => '#112233',
        'secondary_color' => '#445566',
        'status' => 'active',
    ])->assertRedirect(route('companies.index'));

    $this->assertDatabaseHas('companies', [
        'ruc' => '20999999999',
        'name' => 'Nueva Empresa',
    ]);
});
