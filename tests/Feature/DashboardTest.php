<?php

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create();
    $company = Company::factory()->create();
    $role = Role::findByName('Super Admin', 'web');

    $user->companies()->attach($company, [
        'role_id' => $role->id,
        'active' => true,
        'default_company' => true,
    ]);

    setPermissionsTeamId($company->id);
    $user->assignRole($role);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('Panel Ejecutivo')
        ->assertSee('Dashboard');
});
