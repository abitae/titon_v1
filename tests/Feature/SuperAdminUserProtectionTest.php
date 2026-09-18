<?php

use App\Actions\Users\SyncUserCompanies;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function makeCompanyUser(string $roleName, $company): User
{
    $user = User::factory()->create();
    $role = Role::findByName($roleName, 'web');

    $user->companies()->attach($company, [
        'role_id' => $role->id,
        'active' => true,
        'default_company' => true,
    ]);

    setPermissionsTeamId($company->id);
    $user->assignRole($role);

    return $user;
}

test('super admin does not appear in the users list', function () {
    ['user' => $admin, 'company' => $company] = authenticateWithCompany('Administrador');
    $superAdmin = makeCompanyUser('Super Admin', $company);
    $regularUser = makeCompanyUser('Consulta', $company);

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee($regularUser->email)
        ->assertDontSee($superAdmin->email);
});

test('super admin has every registered permission', function () {
    ['user' => $superAdmin] = authenticateWithCompany('Super Admin');

    expect($superAdmin->isSuperAdmin())->toBeTrue();

    Permission::query()->pluck('name')->each(function (string $permission) use ($superAdmin): void {
        expect($superAdmin->can($permission))->toBeTrue();
    });
});

test('super admin cannot be edited or deleted', function () {
    ['user' => $superAdmin, 'company' => $company] = authenticateWithCompany('Super Admin');
    $admin = makeCompanyUser('Administrador', $company);

    $this->actingAs($superAdmin)
        ->get(route('users.edit', $superAdmin))
        ->assertForbidden();

    $this->actingAs($superAdmin)
        ->delete(route('users.destroy', $superAdmin))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('users.edit', $superAdmin))
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $superAdmin))
        ->assertForbidden();

    expect(User::query()->whereKey($superAdmin->id)->exists())->toBeTrue();
});

test('super admin role cannot be assigned or changed from the users form', function () {
    ['user' => $admin, 'company' => $company] = authenticateWithCompany('Administrador');
    $superAdminRole = Role::findByName(User::SUPER_ADMIN_ROLE, 'web');

    $this->actingAs($admin)
        ->get(route('users.create'))
        ->assertOk()
        ->assertDontSee('Super Admin')
        ->assertSee('Consulta');

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Nuevo Super Admin',
            'email' => 'nuevo-super-admin@open9.dev',
            'password' => 'password',
            'password_confirmation' => 'password',
            'company_ids' => [$company->id],
            'role_ids' => [$company->id => $superAdminRole->id],
            'active_company_ids' => [$company->id],
            'default_company_id' => $company->id,
        ])
        ->assertSessionHasErrors(['role_ids.'.$company->id]);

    $this->assertDatabaseMissing('users', [
        'email' => 'nuevo-super-admin@open9.dev',
    ]);
});

test('syncing companies cannot remove or change the super admin role', function () {
    ['user' => $superAdmin, 'company' => $company] = authenticateWithCompany('Super Admin');
    $consultaRole = Role::findByName('Consulta', 'web');
    $superAdminRoleId = User::superAdminRoleId();

    app(SyncUserCompanies::class)->handle(
        $superAdmin,
        [$company->id],
        [$company->id => $consultaRole->id],
        [$company->id],
        $company->id,
    );

    $superAdmin->unsetRelation('roles')->unsetRelation('permissions')->unsetRelation('companies');

    expect((int) $superAdmin->companies()->first()?->pivot->role_id)->toBe($superAdminRoleId)
        ->and($superAdmin->isSuperAdmin())->toBeTrue()
        ->and($superAdmin->hasRole(User::SUPER_ADMIN_ROLE))->toBeTrue();
});
