<?php

use App\Actions\Deployment\ResetSystemMode;
use App\Livewire\Settings\ManageDeploymentMode;
use App\Models\ApplicationSetting;
use App\Models\CatalogItem;
use App\Models\Company;
use App\Models\FleetEquipment;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function actingAsSeededAdmin(): User
{
    $user = User::query()->where('email', 'admin@open9.dev')->firstOrFail();
    $company = Company::query()->orderBy('id')->firstOrFail();

    test()->actingAs($user);
    session([CompanyContext::SESSION_KEY => $company->id]);
    setPermissionsTeamId($company->id);

    return $user;
}

test('super admin can access deployment mode page and regular users cannot', function () {
    $this->seed(PermissionSeeder::class);

    ['user' => $superAdmin] = authenticateWithCompany('Super Admin');

    $this->actingAs($superAdmin)
        ->get(route('settings.deployment-mode'))
        ->assertOk()
        ->assertSee('Produccion');

    ['user' => $regularUser, 'company' => $company] = authenticateWithCompany('Consulta');

    $this->actingAs($regularUser)
        ->withSession([CompanyContext::SESSION_KEY => $company->id])
        ->get(route('settings.deployment-mode'))
        ->assertForbidden();
});

test('invalid confirmation does not change deployment mode', function () {
    $this->seed(DatabaseSeeder::class);
    actingAsSeededAdmin();

    $projectCount = Project::query()->count();

    Livewire::test(ManageDeploymentMode::class)
        ->call('openProductionConfirmation')
        ->set('confirmation', 'WRONG')
        ->call('confirmModeChange')
        ->assertHasErrors(['confirmation']);

    expect(Project::query()->count())->toBe($projectCount)
        ->and(ApplicationSetting::query()->first()?->deployment_mode)->toBe(ResetSystemMode::Development);
});

test('production mode removes demo data and preserves base configuration', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = actingAsSeededAdmin();

    Livewire::test(ManageDeploymentMode::class)
        ->call('openProductionConfirmation')
        ->set('confirmation', 'PRODUCCION')
        ->call('confirmModeChange')
        ->assertHasNoErrors();

    expect(ApplicationSetting::query()->first()?->deployment_mode)->toBe(ResetSystemMode::Production)
        ->and(User::query()->pluck('email')->all())->toBe([$admin->email])
        ->and(Company::query()->count())->toBe(3)
        ->and(Role::query()->where('name', 'Super Admin')->exists())->toBeTrue()
        ->and(CatalogItem::query()->count())->toBeGreaterThan(0)
        ->and(Project::query()->count())->toBe(0)
        ->and(Supplier::query()->count())->toBe(0)
        ->and(FleetEquipment::query()->count())->toBe(0);
});

test('development mode reinserts existing demo seeder data', function () {
    $this->seed(DatabaseSeeder::class);
    actingAsSeededAdmin();

    Livewire::test(ManageDeploymentMode::class)
        ->call('openProductionConfirmation')
        ->set('confirmation', 'PRODUCCION')
        ->call('confirmModeChange')
        ->call('openDevelopmentConfirmation')
        ->set('confirmation', 'DESARROLLO')
        ->call('confirmModeChange')
        ->assertHasNoErrors();

    expect(ApplicationSetting::query()->first()?->deployment_mode)->toBe(ResetSystemMode::Development)
        ->and(User::query()->count())->toBeGreaterThan(1)
        ->and(Project::query()->count())->toBeGreaterThan(0)
        ->and(Supplier::query()->count())->toBeGreaterThan(0)
        ->and(FleetEquipment::query()->count())->toBeGreaterThan(0);
});
