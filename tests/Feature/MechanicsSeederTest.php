<?php

use App\Models\Company;
use App\Models\FleetEquipment;
use App\Models\FleetSparePart;
use App\Models\FleetWorkOrder;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\CompanySeeder;
use Database\Seeders\DemoOperationalSeeder;
use Database\Seeders\MechanicsSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('mechanics seeder creates demo fleet data per company', function () {
    $this->seed([
        PermissionSeeder::class,
        CompanySeeder::class,
        UserSeeder::class,
        CatalogSeeder::class,
        DemoOperationalSeeder::class,
        MechanicsSeeder::class,
    ]);

    $company = Company::query()->first();

    expect($company)->not->toBeNull()
        ->and(FleetEquipment::withoutGlobalScopes()->where('company_id', $company->id)->count())->toBe(5)
        ->and(FleetWorkOrder::withoutGlobalScopes()->where('company_id', $company->id)->count())->toBe(4)
        ->and(FleetSparePart::withoutGlobalScopes()->where('company_id', $company->id)->count())->toBe(3);
});

test('mechanics seeder is idempotent per company', function () {
    $this->seed([
        PermissionSeeder::class,
        CompanySeeder::class,
        UserSeeder::class,
        CatalogSeeder::class,
        DemoOperationalSeeder::class,
        MechanicsSeeder::class,
    ]);

    $company = Company::query()->first();
    $countBefore = FleetEquipment::withoutGlobalScopes()->where('company_id', $company->id)->count();

    $this->seed(MechanicsSeeder::class);

    expect(FleetEquipment::withoutGlobalScopes()->where('company_id', $company->id)->count())->toBe($countBefore);
});
