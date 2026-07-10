<?php

use App\Enums\CorrelativeSubject;
use App\Livewire\Settings\ManageCorrelativeFormats;
use App\Models\CompanyCorrelativeFormat;
use Livewire\Livewire;
use Tests\Support\AuthenticatesWithCompany;

uses(AuthenticatesWithCompany::class);

beforeEach(function () {
    $this->authenticateWithCompany('Super Admin');
});

test('correlatives page shows reset action for editors', function () {
    $this->get(route('settings.correlatives'))
        ->assertOk()
        ->assertSee('Restablecer');
});

test('correlatives row can be reset to default configuration', function () {
    Livewire::test(ManageCorrelativeFormats::class);

    $format = CompanyCorrelativeFormat::query()
        ->where('company_id', $this->company->id)
        ->where('subject', CorrelativeSubject::Project->value)
        ->where('series', '')
        ->firstOrFail();

    $format->update([
        'suffix' => 'CUSTOM',
        'template' => '{prefix}-CUSTOM-{number}',
        'pad_length' => 4,
    ]);

    Livewire::test(ManageCorrelativeFormats::class)
        ->call('resetRow', $format->id)
        ->assertHasNoErrors();

    $format->refresh();

    $defaults = CorrelativeSubject::Project->defaultFormat();

    expect($format->suffix)->toBe($defaults['suffix'])
        ->and($format->template)->toBe($defaults['template'])
        ->and($format->pad_length)->toBe($defaults['pad_length']);
});
