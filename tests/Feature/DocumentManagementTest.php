<?php

use App\Enums\CatalogType;
use App\Enums\DocumentStatus;
use App\Livewire\Documents\ManageInbox;
use App\Livewire\Documents\ShowDocument;
use App\Models\CatalogItem;
use App\Models\Company;
use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->company = Company::factory()->create();
    $this->user = User::factory()->create();
    $this->secondUser = User::factory()->create();
    $this->role = Role::findByName('Super Admin', 'web');

    foreach ([$this->user, $this->secondUser] as $user) {
        $user->companies()->attach($this->company, [
            'role_id' => $this->role->id,
            'active' => true,
            'default_company' => $user->is($this->user),
        ]);
    }

    setPermissionsTeamId($this->company->id);
    $this->user->assignRole($this->role);
    $this->secondUser->assignRole($this->role);

    $this->actingAs($this->user);
    session([CompanyContext::SESSION_KEY => $this->company->id]);
    setPermissionsTeamId($this->company->id);

    $this->project = Project::factory()->create([
        'company_id' => $this->company->id,
        'responsible_user_id' => $this->user->id,
    ]);

    $this->documentType = CatalogItem::factory()->create([
        'company_id' => $this->company->id,
        'type' => CatalogType::DocumentType->value(),
        'name' => 'Carta',
    ]);

    $this->originArea = CatalogItem::factory()->create([
        'company_id' => $this->company->id,
        'type' => CatalogType::Area->value(),
        'name' => 'Mesa de partes',
    ]);

    $this->destinationArea = CatalogItem::factory()->create([
        'company_id' => $this->company->id,
        'type' => CatalogType::Area->value(),
        'name' => 'Gerencia',
    ]);
});

test('documents can be registered with media attachments for the active company', function () {
    Storage::fake('public');

    Livewire::test(ManageInbox::class)
        ->call('openCreateModal')
        ->set('code', 'DOC-001')
        ->set('document_number', 'CARTA-2026-001')
        ->set('work_project_id', $this->project->id)
        ->set('document_type_id', $this->documentType->id)
        ->set('subject', 'Solicitud de valorizacion')
        ->set('description', 'Documento de prueba')
        ->set('origin_area_id', $this->originArea->id)
        ->set('destination_area_id', $this->destinationArea->id)
        ->set('current_user_id', $this->user->id)
        ->set('priority', 'alta')
        ->set('issue_date', now()->toDateString())
        ->set('reception_date', now()->toDateString())
        ->set('due_date', now()->addDays(3)->toDateString())
        ->set('observations', 'Observacion inicial del expediente')
        ->set('attachments', [UploadedFile::fake()->create('solicitud.pdf', 200, 'application/pdf')])
        ->call('saveDocument')
        ->assertHasNoErrors();

    $document = Document::query()
        ->where('company_id', $this->company->id)
        ->where('document_number', 'CARTA-2026-001')
        ->firstOrFail();

    expect($document->company_id)->toBe($this->company->id);
    expect($document->document_number)->toBe('CARTA-2026-001');
    expect($document->reception_date)->not->toBeNull();
    expect($document->observations)->toBe('Observacion inicial del expediente');
    expect($document->getMedia('attachments'))->toHaveCount(1);

    $this->assertDatabaseHas('document_movements', [
        'company_id' => $this->company->id,
        'document_id' => $document->id,
        'action' => 'registered',
        'to_status' => DocumentStatus::Registered->value(),
    ]);
});

test('document workflow actions create audit records and status transitions', function () {
    $document = Document::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'created_by_user_id' => $this->user->id,
        'code' => 'DOC-002',
        'document_type_id' => $this->documentType->id,
        'subject' => 'Control contractual',
        'description' => 'Seguimiento del expediente',
        'origin_area_id' => $this->originArea->id,
        'destination_area_id' => $this->destinationArea->id,
        'current_user_id' => $this->user->id,
        'status' => DocumentStatus::Registered->value(),
        'priority' => 'media',
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(5)->toDateString(),
    ]);

    Livewire::test(ShowDocument::class, ['document' => $document])
        ->call('receiveDocument')
        ->set('derive_destination_area_id', $this->originArea->id)
        ->set('derive_current_user_id', $this->secondUser->id)
        ->set('derive_notes', 'Enviar a control interno')
        ->call('deriveDocument')
        ->set('observation', 'Falta foliar el anexo.')
        ->call('observeDocument')
        ->set('approval_comments', 'Conforme para continuar.')
        ->call('approveDocument')
        ->set('close_notes', 'Expediente completado.')
        ->call('closeDocument')
        ->assertHasNoErrors();

    $document->refresh();

    expect($document->status)->toBe(DocumentStatus::Closed->value());

    $this->assertDatabaseHas('document_observations', [
        'document_id' => $document->id,
        'status_after' => DocumentStatus::Observed->value(),
    ]);

    $this->assertDatabaseHas('document_approvals', [
        'document_id' => $document->id,
        'decision' => 'approved',
    ]);

    expect($document->movements()->count())->toBe(5);
});

test('document enhanced workflow supports attended archived reopened and cancelled states with movement attachments', function () {
    Storage::fake('public');

    $document = Document::factory()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'created_by_user_id' => $this->user->id,
        'current_user_id' => $this->user->id,
        'origin_area_id' => $this->originArea->id,
        'destination_area_id' => $this->destinationArea->id,
        'document_type_id' => $this->documentType->id,
        'status' => DocumentStatus::Registered->value(),
    ]);

    Livewire::test(ShowDocument::class, ['document' => $document])
        ->set('movementAttachments', [UploadedFile::fake()->create('recepcion.pdf', 120, 'application/pdf')])
        ->call('receiveDocument')
        ->set('process_notes', 'Documento atendido por el responsable.')
        ->call('attendDocument')
        ->set('archive_notes', 'Se archiva por atencion completa.')
        ->call('archiveDocument')
        ->set('reopen_notes', 'Se requiere una revision adicional.')
        ->call('reopenDocument')
        ->set('annulment_reason', 'Documento duplicado.')
        ->call('cancelDocument')
        ->assertHasNoErrors();

    $document->refresh();

    expect($document->status)->toBe(DocumentStatus::Cancelled->value());
    expect($document->cancelled_at)->not->toBeNull();
    expect($document->reception_date)->not->toBeNull();
    expect($document->attended_at)->not->toBeNull();
    expect($document->archived_at)->not->toBeNull();
    expect($document->movements()->count())->toBe(5);
    expect($document->movements()->latest('id')->first()?->action)->toBe('cancelled');
    expect($document->movements()->oldest('id')->first()?->getMedia('attachments'))->toHaveCount(1);
});

test('document pages render for authorized users', function () {
    $document = Document::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'created_by_user_id' => $this->user->id,
        'code' => 'DOC-003',
        'document_type_id' => $this->documentType->id,
        'subject' => 'Memorando',
        'origin_area_id' => $this->originArea->id,
        'destination_area_id' => $this->destinationArea->id,
        'current_user_id' => $this->user->id,
        'status' => DocumentStatus::Registered->value(),
        'priority' => 'media',
    ]);

    $this->get(route('modules.documents'))->assertOk()->assertSee('Bandeja de entrada');
    $this->get(route('documents.outbox'))->assertOk()->assertSee('Bandeja de salida');
    $this->get(route('documents.projects'))->assertOk()->assertSee('Documentos por obra');
    $this->get(route('documents.show', $document))->assertOk()->assertSee('Detalle del documento');
    $this->get(route('documents.timeline', $document))->assertOk()->assertSee('Timeline del documento');
});
