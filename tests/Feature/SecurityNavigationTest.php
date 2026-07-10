<?php

use App\Models\Company;
use App\Models\Permission;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use App\Services\Navigation\AppNavigation;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('permissions are seeded with short descriptions', function () {
    $this->seed(PermissionSeeder::class);

    $permission = Permission::query()->where('name', 'users.ver')->first();
    $deploymentPermission = Permission::query()->where('name', 'deployment.ver')->first();

    expect($permission)->not->toBeNull()
        ->and($permission->description)->toBe('Ver en Usuarios')
        ->and($deploymentPermission)->not->toBeNull()
        ->and($deploymentPermission->description)->toBe('Ver en Produccion');
});

test('sidebar exposes roles and permissions under security for authorized users', function () {
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
    session([CompanyContext::SESSION_KEY => $company->id]);

    $seguridad = collect(app(AppNavigation::class)->sidebarGroups())->firstWhere('heading', 'Seguridad');

    expect(collect($seguridad['items'])->pluck('label')->all())->toContain('Roles', 'Permisos');
});

test('sidebar hides security items without view permission', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create();
    $company = Company::factory()->create();
    $role = Role::findByName('Compras', 'web');

    $user->companies()->attach($company, [
        'role_id' => $role->id,
        'active' => true,
        'default_company' => true,
    ]);

    setPermissionsTeamId($company->id);
    $user->assignRole($role);

    $this->actingAs($user);
    session([CompanyContext::SESSION_KEY => $company->id]);

    $seguridad = collect(app(AppNavigation::class)->sidebarGroups())->firstWhere('heading', 'Seguridad');

    expect($seguridad)->not->toBeNull()
        ->and(collect($seguridad['items'])->pluck('label')->all())
        ->not->toContain('Roles', 'Permisos', 'Empresas', 'Usuarios');
});

test('roles page is protected and accessible for administrators', function () {
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
    session([CompanyContext::SESSION_KEY => $company->id]);

    $this->get(route('security.roles'))
        ->assertOk()
        ->assertSee('Roles del sistema');

    $viewer = User::factory()->create();
    $viewerRole = Role::findByName('Compras', 'web');

    $viewer->companies()->attach($company, [
        'role_id' => $viewerRole->id,
        'active' => true,
        'default_company' => true,
    ]);

    setPermissionsTeamId($company->id);
    $viewer->assignRole($viewerRole);

    $this->actingAs($viewer);
    session([CompanyContext::SESSION_KEY => $company->id]);

    $this->get(route('security.roles'))
        ->assertForbidden();
});

test('permissions page lists permission descriptions', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create();
    $company = Company::factory()->create();
    $role = Role::findByName('Gerencia', 'web');

    $user->companies()->attach($company, [
        'role_id' => $role->id,
        'active' => true,
        'default_company' => true,
    ]);

    setPermissionsTeamId($company->id);
    $user->assignRole($role);

    $this->actingAs($user);
    session([CompanyContext::SESSION_KEY => $company->id]);

    $this->get(route('security.permissions'))
        ->assertOk()
        ->assertSee('roles.ver')
        ->assertSee('Ver en Roles');
});

test('dashboard route requires dashboard view permission', function () {
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
    session([CompanyContext::SESSION_KEY => $company->id]);

    $this->get(route('dashboard'))
        ->assertOk();

    $role->revokePermissionTo('dashboard.ver');

    $this->actingAs($user);
    session([CompanyContext::SESSION_KEY => $company->id]);

    $this->get(route('dashboard'))
        ->assertForbidden();
});
