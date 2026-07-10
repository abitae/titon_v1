<?php

use App\Models\ApplicationSetting;
use App\Models\Company;
use App\Models\User;
use App\Services\Application\ApplicationSettingsManager;
use App\Services\Branding\PlatformBranding;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

test('login screen displays application icon instead of company logo', function () {
    $companyLogoPath = 'companies/logos/empresa.png';
    $applicationLogoPath = 'application-settings/app-icon.png';

    Storage::disk('public')->put($companyLogoPath, 'company');
    Storage::disk('public')->put($applicationLogoPath, 'application');

    Company::factory()->create([
        'name' => 'Titon Infraestructura',
        'logo' => $companyLogoPath,
    ]);

    ApplicationSetting::query()->updateOrCreate(
        ['id' => 1],
        [
            'application_name' => 'Titon ERP',
            'logo_path' => $applicationLogoPath,
        ],
    );

    Cache::forget(ApplicationSettingsManager::CACHE_KEY);

    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('/storage/'.$applicationLogoPath, false);
    $response->assertSee('Titon ERP', false);
    $response->assertDontSee('/storage/'.$companyLogoPath, false);
    $response->assertDontSee('Titon Infraestructura', false);
});

test('platform branding uses application settings only', function () {
    $companyLogoPath = 'companies/logos/company.png';
    $applicationLogoPath = 'application-settings/application.png';

    Storage::disk('public')->put($companyLogoPath, 'company');
    Storage::disk('public')->put($applicationLogoPath, 'application');

    Company::factory()->create([
        'name' => 'Empresa Principal',
        'logo' => $companyLogoPath,
    ]);

    ApplicationSetting::query()->updateOrCreate(
        ['id' => 1],
        [
            'application_name' => 'Aplicacion Global',
            'logo_path' => $applicationLogoPath,
        ],
    );

    $branding = app(PlatformBranding::class);

    expect($branding->name())->toBe('Aplicacion Global')
        ->and($branding->logoUrl())->toBe('/storage/'.$applicationLogoPath)
        ->and($branding->faviconUrl())->toBe('/storage/'.$applicationLogoPath);
});

test('authorized users can upload company logo through empresa form', function () {
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

    $targetCompany = Company::factory()->create(['name' => 'Empresa Con Logo']);

    $this->actingAs($user)
        ->put(route('companies.update', $targetCompany), [
            'name' => 'Empresa Con Logo',
            'business_name' => 'Empresa Con Logo S.A.C.',
            'ruc' => $targetCompany->ruc,
            'address' => 'Av. Principal 123',
            'phone' => '012345678',
            'email' => 'logo@empresa.pe',
            'primary_color' => '#112233',
            'secondary_color' => '#445566',
            'status' => 'active',
            'logo' => UploadedFile::fake()->create('logo.png', 100, 'image/png'),
        ])
        ->assertRedirect(route('companies.index'));

    $targetCompany->refresh();

    expect($targetCompany->logo)->not->toBeNull();
    Storage::disk('public')->assertExists($targetCompany->logo);
});
