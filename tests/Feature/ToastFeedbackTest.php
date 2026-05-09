<?php

use App\Livewire\Projects\ManageProjects;
use App\Models\Company;
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

function makeAdministratorUser(): array
{
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

    return [$user, $company];
}

test('switching active company flashes a success toast', function () {
    [$user, $companyA] = makeAdministratorUser();
    $companyB = Company::factory()->create();
    $role = Role::findByName('Administrador', 'web');

    $user->companies()->attach($companyB, [
        'role_id' => $role->id,
        'active' => true,
        'default_company' => false,
    ]);

    $this->actingAs($user)
        ->withSession([CompanyContext::SESSION_KEY => $companyA->id])
        ->post(route('active-company.store'), [
            'company_id' => $companyB->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('toasts.0.dataset.variant', 'success')
        ->assertSessionHas('toasts.0.slots.text', 'Empresa activa actualizada.');
});

test('project management dispatches a toast after saving', function () {
    [$user, $company] = makeAdministratorUser();

    $this->actingAs($user);
    session([CompanyContext::SESSION_KEY => $company->id]);
    setPermissionsTeamId($company->id);

    Livewire::test(ManageProjects::class)
        ->set('code', 'OBR-901')
        ->set('name', 'Nueva Obra')
        ->set('status', 'planificada')
        ->call('saveProject')
        ->assertDispatched('toast-show');
});
