<?php

use App\Models\AccountsPayable;
use App\Models\Company;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\SupplierQuotation;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\CompanySeeder;
use Database\Seeders\DemoOperationalSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('demo operational seeder creates full procurement flow per company', function () {
    $this->seed(PermissionSeeder::class);
    $this->seed(CompanySeeder::class);
    $this->seed(UserSeeder::class);
    $this->seed(CatalogSeeder::class);
    $this->seed(DemoOperationalSeeder::class);

    $company = Company::query()->first();

    expect(Project::withoutGlobalScopes()->where('company_id', $company->id)->count())->toBeGreaterThan(0);
    expect(Requirement::withoutGlobalScopes()->where('company_id', $company->id)->count())->toBeGreaterThanOrEqual(2);
    expect(SupplierQuotation::withoutGlobalScopes()->where('company_id', $company->id)->count())->toBeGreaterThanOrEqual(2);
    expect(AccountsPayable::withoutGlobalScopes()->where('company_id', $company->id)->where('status', 'pagada')->exists())->toBeTrue();

    $requirement = Requirement::withoutGlobalScopes()
        ->where('company_id', $company->id)
        ->whereNotNull('code')
        ->first();

    expect($requirement?->code)->toContain('REQ');
});
