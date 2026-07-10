<?php

use App\Livewire\Frontend\ManageSiteContent;
use App\Models\ApplicationSetting;
use App\Models\Company;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Application\ApplicationSettingsManager;
use App\Services\Companies\CompanyContext;
use App\Services\Frontend\SiteContentService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SiteContentSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    $this->seed(SiteContentSeeder::class);
    Storage::fake('public');
});

function makeSuperAdminForSiteContent(): array
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

test('super admin can access site content admin with brand tab', function () {
    [$user, $company] = makeSuperAdminForSiteContent();

    $this->actingAs($user)
        ->withSession([CompanyContext::SESSION_KEY => $company->id])
        ->get(route('admin.site-content'))
        ->assertOk()
        ->assertSee('Marca');
});

test('super admin can update site brand settings', function () {
    [$user, $company] = makeSuperAdminForSiteContent();

    $this->actingAs($user);
    session([CompanyContext::SESSION_KEY => $company->id]);

    $logo = UploadedFile::fake()->image('logo.png');
    $favicon = UploadedFile::fake()->image('favicon.png', 32, 32);

    Livewire::test(ManageSiteContent::class)
        ->call('selectGroup', 'brand')
        ->set('brandName', 'Titon Web')
        ->set('brandLogo', $logo)
        ->set('brandFavicon', $favicon)
        ->call('saveBrand')
        ->assertHasNoErrors();

    $brand = SiteSetting::query()->where('key', 'brand')->first();

    expect($brand)->not->toBeNull()
        ->and($brand->title)->toBe('Titon Web')
        ->and($brand->image_path)->not->toBeNull()
        ->and($brand->favicon_path)->not->toBeNull();

    Storage::disk('public')->assertExists($brand->image_path);
    Storage::disk('public')->assertExists($brand->favicon_path);

    expect(app(SiteContentService::class)->brandName())->toBe('Titon Web');
});

test('admin panel displays site brand logo', function () {
    [$user, $company] = makeSuperAdminForSiteContent();

    SiteSetting::query()->updateOrCreate(
        ['key' => 'brand'],
        [
            'title' => 'Titon Admin',
            'image_path' => 'site/brand/logo.png',
            'is_active' => true,
            'sort_order' => 0,
        ],
    );

    Storage::disk('public')->put('site/brand/logo.png', 'fake-logo');

    ApplicationSetting::query()->updateOrCreate(
        ['id' => 1],
        [
            'application_name' => 'Titon Admin',
            'logo_path' => 'site/brand/logo.png',
        ],
    );

    Cache::forget(ApplicationSettingsManager::CACHE_KEY);
    app(SiteContentService::class)->forgetSection('brand');

    $this->actingAs($user)
        ->withSession([CompanyContext::SESSION_KEY => $company->id])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Titon Admin')
        ->assertSee('/storage/site/brand/logo.png', false);
});

test('frontend uses site brand name and favicon', function () {
    SiteSetting::query()->updateOrCreate(
        ['key' => 'brand'],
        [
            'title' => 'Marca Titon',
            'is_active' => true,
            'sort_order' => 0,
            'favicon_path' => 'site/brand/favicon.png',
        ],
    );

    Storage::disk('public')->put('site/brand/favicon.png', 'fake-image');

    app(SiteContentService::class)->forgetSection('brand');

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Marca Titon');
    $response->assertSee('/storage/site/brand/favicon.png', false);
});

test('home page displays card section images', function () {
    $setting = SiteSetting::query()->where('key', 'home.cards.nosotros')->firstOrFail();

    Storage::disk('public')->put('site/card-nosotros.jpg', 'fake-card-image');

    $setting->update(['image_path' => 'site/card-nosotros.jpg']);

    app(SiteContentService::class)->forgetSection('home.cards.nosotros');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('/storage/site/card-nosotros.jpg', false);
});
