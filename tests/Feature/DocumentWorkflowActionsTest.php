<?php

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\User;
use App\Services\Documents\DocumentWorkflowActions;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    Permission::findOrCreate('documents.editar', 'web');
    Permission::findOrCreate('documents.aprobar', 'web');
});

test('registered document assigned to user exposes receive and derive actions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('documents.editar');

    $document = Document::factory()->make([
        'status' => DocumentStatus::Registered->value(),
        'current_user_id' => $user->id,
    ]);
    $document->id = 1;

    $actions = app(DocumentWorkflowActions::class)->available($document, $user);

    expect($actions)->toContain('receive', 'derive', 'observe', 'cancel')
        ->not->toContain('approve', 'reopen');
});

test('closed document only allows reopen for editors', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('documents.editar');

    $document = Document::factory()->make([
        'status' => DocumentStatus::Closed->value(),
        'current_user_id' => 99,
    ]);

    expect(app(DocumentWorkflowActions::class)->available($document, $user))->toBe(['reopen']);
});

test('cancelled document exposes no actions', function () {
    $user = User::factory()->create();

    $document = Document::factory()->make([
        'status' => DocumentStatus::Cancelled->value(),
        'current_user_id' => $user->id,
    ]);

    expect(app(DocumentWorkflowActions::class)->available($document, $user))->toBe([]);
});
