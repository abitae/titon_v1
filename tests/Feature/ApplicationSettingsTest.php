<?php

use App\Http\Middleware\EnsureActiveCompany;
use App\Http\Middleware\SetActiveCompanyContext;
use App\Models\ApplicationSetting;
use App\Models\Company;
use App\Models\User;
use App\Services\Application\ApplicationSettingsManager;
use App\Services\Companies\CompanyContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    Storage::fake('public');
});

function makeSuperAdminUser(): array
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

test('application settings page is displayed for super admin', function () {
    [$user, $company] = makeSuperAdminUser();

    $this->actingAs($user)
        ->withSession([CompanyContext::SESSION_KEY => $company->id])
        ->get(route('settings.application'))
        ->assertOk()
        ->assertSee('Application')
        ->assertSee('General identity');
});

test('application settings can be updated', function () {
    [$user, $company] = makeSuperAdminUser();

    $this->actingAs($user);
    session([CompanyContext::SESSION_KEY => $company->id]);

    $response = Livewire::test('pages::settings.application')
        ->set('application_name', 'Titon ERP')
        ->set('logo', UploadedFile::fake()->image('logo.png', 300, 300))
        ->call('saveApplicationSettings');

    $response->assertHasNoErrors();

    $settings = app(ApplicationSettingsManager::class)->current();

    expect($settings->application_name)->toBe('Titon ERP');
    expect($settings->logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($settings->logo_path);
});

test('application settings page is forbidden for regular users', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $role = Role::findByName('Consulta', 'web');

    $user->companies()->attach($company, [
        'role_id' => $role->id,
        'active' => true,
        'default_company' => true,
    ]);

    setPermissionsTeamId($company->id);
    $user->assignRole($role);

    $this->actingAs($user)
        ->withSession([CompanyContext::SESSION_KEY => $company->id])
        ->get(route('settings.application'))
        ->assertForbidden();
});

test('application settings manager recovers from invalid cached payloads', function () {
    Cache::forever(ApplicationSettingsManager::CACHE_KEY, 'invalid');

    $settings = app(ApplicationSettingsManager::class)->current();

    expect($settings)->toBeInstanceOf(ApplicationSetting::class);
    expect($settings->application_name)->not->toBe('');
});

test('application settings uploads preserve active company middleware', function () {
    expect(app('livewire')->getPersistentMiddleware())
        ->toContain(SetActiveCompanyContext::class)
        ->toContain(EnsureActiveCompany::class);

    expect((array) FileUploadConfiguration::middleware())
        ->toContain('auth')
        ->toContain('active.company.context');
});
