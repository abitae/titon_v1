<?php

use App\Livewire\Frontend\ManageShowcaseProjects;
use App\Models\Company;
use App\Models\ShowcaseProject;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\PermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

function makeSuperAdminForFrontend(): array
{
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

    return [$user, $company];
}

test('super admin can access showcase projects admin', function () {
    [$user, $company] = makeSuperAdminForFrontend();

    $this->actingAs($user)
        ->withSession([CompanyContext::SESSION_KEY => $company->id])
        ->get(route('admin.showcase-projects'))
        ->assertOk()
        ->assertSee('Portafolio web');
});

test('regular user cannot access showcase projects admin', function () {
    authenticateWithCompany('Consulta');

    $this->get(route('admin.showcase-projects'))
        ->assertForbidden();
});

test('super admin can create showcase project', function () {
    [$user, $company] = makeSuperAdminForFrontend();

    $this->actingAs($user);
    session([CompanyContext::SESSION_KEY => $company->id]);

    Livewire::test(ManageShowcaseProjects::class)
        ->call('openCreateModal')
        ->set('projectTitle', 'Nueva obra pública')
        ->set('slug', 'nueva-obra-publica')
        ->set('summary', 'Resumen del proyecto.')
        ->set('city', 'Lima')
        ->set('is_published', true)
        ->call('saveProject')
        ->assertHasNoErrors();

    expect(ShowcaseProject::query()->where('slug', 'nueva-obra-publica')->exists())->toBeTrue();
});
