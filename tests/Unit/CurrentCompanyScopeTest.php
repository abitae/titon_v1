<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('current company scope returns no rows when session company is missing', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create();

    Project::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);
    session()->forget(CompanyContext::SESSION_KEY);

    expect(Project::query()->count())->toBe(0);
});
