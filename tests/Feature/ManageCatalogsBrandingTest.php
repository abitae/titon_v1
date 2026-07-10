<?php

use App\Livewire\Settings\ManageCatalogs;
use App\Models\ApplicationSetting;
use App\Services\Application\ApplicationSettingsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\AuthenticatesWithCompany;

uses(RefreshDatabase::class, AuthenticatesWithCompany::class);

beforeEach(function () {
    Storage::fake('public');
    $this->authenticateWithCompany('Super Admin');
});

test('manage catalogs displays saved application logo url', function () {
    $logoPath = 'application-settings/icon.png';
    Storage::disk('public')->put($logoPath, 'logo-binary');

    ApplicationSetting::query()->updateOrCreate(
        ['id' => 1],
        [
            'application_name' => 'Titon ERP',
            'logo_path' => $logoPath,
        ],
    );

    Cache::forget(ApplicationSettingsManager::CACHE_KEY);

    $logoUrl = '/storage/'.$logoPath;

    Livewire::test(ManageCatalogs::class)
        ->assertSet('currentApplicationLogoUrl', $logoUrl)
        ->assertSee($logoUrl, false);

    $this->get(route('settings.catalogs'))
        ->assertOk()
        ->assertSee($logoUrl, false);
});
