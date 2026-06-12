<?php

namespace Tests\Support;

use App\Models\Company;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Role;

trait AuthenticatesWithCompany
{
    protected Company $company;

    protected User $user;

    protected Role $role;

    protected function authenticateWithCompany(string $roleName = 'Super Admin'): void
    {
        $this->seed(PermissionSeeder::class);

        $this->company = Company::factory()->create([
            'correlative_prefix' => 'TITON',
        ]);

        $this->user = User::factory()->create();
        $this->role = Role::findByName($roleName, 'web');

        $this->user->companies()->attach($this->company, [
            'role_id' => $this->role->id,
            'active' => true,
            'default_company' => true,
        ]);

        setPermissionsTeamId($this->company->id);
        $this->user->assignRole($this->role);

        $this->actingAs($this->user);
        session([CompanyContext::SESSION_KEY => $this->company->id]);
        setPermissionsTeamId($this->company->id);
    }
}
