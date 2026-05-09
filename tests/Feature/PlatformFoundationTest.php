<?php

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('authenticated users can access all business modules', function () {
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

    foreach ([
        'modules.documents' => 'Documentos',
        'modules.projects' => 'Obras',
        'modules.suppliers' => 'Proveedores',
        'modules.contracts' => 'Contratos',
        'modules.payments' => 'Pagos',
    ] as $route => $label) {
        $this->get(route($route))
            ->assertOk()
            ->assertSee($label)
            ->assertSee($company->name);
    }
});

test('company policy allows authorized users to manage their company', function () {
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

    expect($user->can('view', $company))->toBeTrue();
    expect($user->can('update', $company))->toBeTrue();
    expect($user->can('delete', $company))->toBeFalse();
});
