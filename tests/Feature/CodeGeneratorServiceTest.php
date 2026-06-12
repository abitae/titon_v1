<?php

use App\Enums\CorrelativeSubject;
use App\Enums\OrderType;
use App\Models\Company;
use App\Models\Project;
use App\Services\Codes\CodeGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('order codes include project series and single oc suffix', function () {
    $company = Company::factory()->create(['correlative_prefix' => 'ACME']);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'code' => 'OBR001',
    ]);

    $generator = app(CodeGeneratorService::class);

    $code = $generator->generate($company, $project, CorrelativeSubject::Order, OrderType::Purchase);

    expect($code)->not->toMatch('/-OC-OC-/');
    expect($code)->toMatch('/^ACME-OBR001-OC-\d{4}-\d{6}$/');
});

test('service order codes use os suffix', function () {
    $company = Company::factory()->create(['correlative_prefix' => 'ACME']);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'code' => 'OBR002',
    ]);

    $generator = app(CodeGeneratorService::class);

    $code = $generator->generate($company, $project, CorrelativeSubject::Order, OrderType::Service);

    expect($code)->toMatch('/^ACME-OBR002-OS-\d{4}-\d{6}$/');
});

test('peek does not advance the issued sequence', function () {
    $company = Company::factory()->create(['correlative_prefix' => 'TST']);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'code' => 'OBR003',
    ]);

    $generator = app(CodeGeneratorService::class);

    $peekOne = $generator->peek($company, $project, CorrelativeSubject::Order, OrderType::Purchase);
    $peekTwo = $generator->peek($company, $project, CorrelativeSubject::Order, OrderType::Purchase);
    $issued = $generator->generate($company, $project, CorrelativeSubject::Order, OrderType::Purchase);

    expect($peekOne)->toBe($peekTwo);
    expect($issued)->toBe($peekOne);
});
