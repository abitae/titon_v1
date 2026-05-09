<?php

use App\Enums\CorrelativeSubject;
use App\Models\Company;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('emite codigos secuenciales por empresa y asunto', function () {
    $company = Company::factory()->create(['correlative_prefix' => 'ACME']);

    $issuer = app(IssueCompanyCorrelativeCode::class);

    $first = $issuer->issue($company, CorrelativeSubject::Document);
    $second = $issuer->issue($company, CorrelativeSubject::Document);

    expect($first)->not->toBe($second);
    expect($first)->toMatch('/^ACME-DOC-\d{4}-\d{6}$/');
    expect($second)->toMatch('/^ACME-DOC-\d{4}-\d{6}$/');
});

test('aisla secuencias entre empresas', function () {
    $a = Company::factory()->create(['correlative_prefix' => 'AAA']);
    $b = Company::factory()->create(['correlative_prefix' => 'BBB']);

    $issuer = app(IssueCompanyCorrelativeCode::class);

    $codeA = $issuer->issue($a, CorrelativeSubject::PurchaseOrder);
    $codeB = $issuer->issue($b, CorrelativeSubject::PurchaseOrder);

    expect($codeA)->toStartWith('AAA-OC-');
    expect($codeB)->toStartWith('BBB-OC-');
});

test('peek no incrementa el correlativo', function () {
    $company = Company::factory()->create(['correlative_prefix' => 'TST']);
    $issuer = app(IssueCompanyCorrelativeCode::class);

    $peekOne = $issuer->peek($company, CorrelativeSubject::ExportedReport);
    $peekTwo = $issuer->peek($company, CorrelativeSubject::ExportedReport);
    $issued = $issuer->issue($company, CorrelativeSubject::ExportedReport);

    expect($peekOne)->toBe($peekTwo);
    expect($issued)->toBe($peekOne);

    expect($issuer->peek($company, CorrelativeSubject::ExportedReport))->not->toBe($issued);
});
