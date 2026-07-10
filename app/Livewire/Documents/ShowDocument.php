<?php

namespace App\Livewire\Documents;

use App\Actions\Documents\TransitionDocument;
use App\Concerns\InteractsWithToast;
use App\Enums\CatalogType;
use App\Enums\DocumentMovementType;
use App\Enums\DocumentStatus;
use App\Models\CatalogItem;
use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\DocumentObservation;
use App\Services\Audit\UserAuditLogger;
use App\Services\Documents\DocumentWorkflowActions;
use App\Services\Documents\DocumentWorkflowTimeline;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class ShowDocument extends Component
{
    use InteractsWithToast, WithFileUploads;

    public string $title = 'Detalle del documento';

    public Document $document;

    public ?int $derive_destination_area_id = null;

    public ?int $derive_current_user_id = null;

    public string $derive_notes = '';

    public string $observation = '';

    public string $process_notes = '';

    public string $archive_notes = '';

    public string $reopen_notes = '';

    public string $approval_comments = '';

    public string $rejection_comments = '';

    public string $close_notes = '';

    public string $annulment_reason = '';

    public array $newAttachments = [];

    public array $movementAttachments = [];

    public ?string $actionModal = null;

    /** @var list<string> */
    protected array $documentRelations = [
        'project',
        'documentType',
        'originArea',
        'destinationArea',
        'createdByUser',
        'currentUser',
        'movements.user',
        'movements.fromArea',
        'movements.toArea',
        'movements.fromUser',
        'movements.toUser',
        'movements.media',
        'movementObservations.user',
        'approvals.user',
        'media',
        'company',
    ];

    public function mount(Document $document): void
    {
        $this->document = $document->load($this->documentRelations);

        $this->derive_destination_area_id = $this->document->destination_area_id;
        $this->derive_current_user_id = $this->document->current_user_id;
        $this->refreshExpiredDocument();
    }

    public function render(DocumentWorkflowTimeline $documentWorkflowTimeline, DocumentWorkflowActions $workflowActions): View
    {
        return view('livewire.documents.show-document', [
            'timeline' => $documentWorkflowTimeline->build($this->document),
            'workflowActions' => $workflowActions->available($this->document, auth()->user()),
            'areas' => CatalogItem::query()->ofType(CatalogType::Area)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'users' => $this->document->company?->users()->wherePivot('active', true)->orderBy('name')->get(['users.id', 'users.name']) ?? collect(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function openActionModal(string $action, DocumentWorkflowActions $workflowActions): void
    {
        abort_unless(in_array($action, $workflowActions->available($this->document, auth()->user()), true), 403);

        $this->actionModal = $action;
    }

    public function closeActionModal(): void
    {
        $this->actionModal = null;
    }

    public function receiveDocument(TransitionDocument $transitionDocument, DocumentWorkflowActions $workflowActions): void
    {
        abort_unless(in_array('receive', $workflowActions->available($this->document, auth()->user()), true), 403);
        $this->validateMovementAttachments();

        $this->afterTransition($transitionDocument->handle(
            document: $this->document,
            actor: auth()->user(),
            action: DocumentMovementType::Received,
            status: DocumentStatus::Received,
            attributes: [
                'current_user_id' => auth()->id(),
                'reception_date' => now()->toDateString(),
            ],
            notes: 'Documento recibido por el usuario actual.',
            attachments: $this->movementAttachments,
        ));

        $this->movementAttachments = [];
        $this->closeActionModal();
        $this->successToast('Documento recibido correctamente.');
    }

    public function sendToReview(TransitionDocument $transitionDocument, DocumentWorkflowActions $workflowActions): void
    {
        abort_unless(in_array('process', $workflowActions->available($this->document, auth()->user()), true), 403);
        $this->validateMovementAttachments();

        $this->afterTransition($transitionDocument->handle(
            document: $this->document,
            actor: auth()->user(),
            action: DocumentMovementType::Derived,
            status: DocumentStatus::InProgress,
            notes: $this->process_notes !== '' ? $this->process_notes : 'Documento marcado en proceso.',
            attachments: $this->movementAttachments,
        ));

        $this->process_notes = '';
        $this->movementAttachments = [];
        $this->closeActionModal();
        $this->successToast('Documento enviado a revision correctamente.');
    }

    public function deriveDocument(TransitionDocument $transitionDocument, DocumentWorkflowActions $workflowActions): void
    {
        abort_unless(in_array('derive', $workflowActions->available($this->document, auth()->user()), true), 403);
        $this->validateMovementAttachments();

        $validated = $this->validateWithToastFeedback([
            'derive_destination_area_id' => ['required', 'integer', 'exists:catalog_items,id'],
            'derive_current_user_id' => ['required', 'integer', 'exists:users,id'],
            'derive_notes' => ['nullable', 'string'],
        ], [], [
            'derive_destination_area_id' => 'area destino',
            'derive_current_user_id' => 'responsable',
            'derive_notes' => 'nota',
        ]);

        $this->afterTransition($transitionDocument->handle(
            document: $this->document,
            actor: auth()->user(),
            action: DocumentMovementType::Derived,
            status: DocumentStatus::Derived,
            attributes: [
                'origin_area_id' => $this->document->destination_area_id ?? $this->document->origin_area_id,
                'destination_area_id' => $validated['derive_destination_area_id'],
                'current_user_id' => $validated['derive_current_user_id'],
            ],
            notes: $validated['derive_notes'] ?: 'Documento derivado a una nueva area.',
            attachments: $this->movementAttachments,
        ));

        $this->derive_notes = '';
        $this->movementAttachments = [];
        $this->closeActionModal();
        $this->successToast('Documento derivado correctamente.');
    }

    public function observeDocument(TransitionDocument $transitionDocument, DocumentWorkflowActions $workflowActions): void
    {
        abort_unless(in_array('observe', $workflowActions->available($this->document, auth()->user()), true), 403);
        $this->validateMovementAttachments();

        $validated = $this->validateWithToastFeedback([
            'observation' => ['required', 'string'],
        ], [], [
            'observation' => 'observacion',
        ]);

        DocumentObservation::query()->create([
            'company_id' => $this->document->company_id,
            'document_id' => $this->document->id,
            'user_id' => auth()->id(),
            'observation' => $validated['observation'],
            'status_after' => DocumentStatus::Observed->value(),
        ]);

        $this->afterTransition($transitionDocument->handle(
            document: $this->document,
            actor: auth()->user(),
            action: DocumentMovementType::Observed,
            status: DocumentStatus::Observed,
            notes: $validated['observation'],
            attachments: $this->movementAttachments,
        ));

        $this->observation = '';
        $this->movementAttachments = [];
        $this->closeActionModal();
        $this->warningToast('Observacion registrada correctamente.');
    }

    public function approveDocument(TransitionDocument $transitionDocument, DocumentWorkflowActions $workflowActions): void
    {
        abort_unless(in_array('approve', $workflowActions->available($this->document, auth()->user()), true), 403);
        $this->validateMovementAttachments();

        DocumentApproval::query()->create([
            'company_id' => $this->document->company_id,
            'document_id' => $this->document->id,
            'user_id' => auth()->id(),
            'decision' => 'approved',
            'comments' => $this->approval_comments,
            'resolved_at' => now(),
        ]);

        $this->afterTransition($transitionDocument->handle(
            document: $this->document,
            actor: auth()->user(),
            action: DocumentMovementType::Approved,
            status: DocumentStatus::Approved,
            notes: $this->approval_comments ?: 'Documento aprobado.',
            attachments: $this->movementAttachments,
        ));

        $this->approval_comments = '';
        $this->movementAttachments = [];
        $this->closeActionModal();
        $this->successToast('Documento aprobado correctamente.');
    }

    public function rejectDocument(TransitionDocument $transitionDocument, DocumentWorkflowActions $workflowActions): void
    {
        abort_unless(in_array('reject', $workflowActions->available($this->document, auth()->user()), true), 403);
        $this->validateMovementAttachments();

        $validated = $this->validateWithToastFeedback([
            'rejection_comments' => ['required', 'string'],
        ], [], [
            'rejection_comments' => 'motivo de rechazo',
        ]);

        DocumentApproval::query()->create([
            'company_id' => $this->document->company_id,
            'document_id' => $this->document->id,
            'user_id' => auth()->id(),
            'decision' => 'rejected',
            'comments' => $validated['rejection_comments'],
            'resolved_at' => now(),
        ]);

        $this->afterTransition($transitionDocument->handle(
            document: $this->document,
            actor: auth()->user(),
            action: DocumentMovementType::Rejected,
            status: DocumentStatus::Rejected,
            notes: $validated['rejection_comments'],
            attachments: $this->movementAttachments,
        ));

        $this->rejection_comments = '';
        $this->movementAttachments = [];
        $this->closeActionModal();
        $this->warningToast('Documento rechazado correctamente.');
    }

    public function attendDocument(TransitionDocument $transitionDocument, DocumentWorkflowActions $workflowActions): void
    {
        abort_unless(in_array('attend', $workflowActions->available($this->document, auth()->user()), true), 403);
        $this->validateMovementAttachments();

        $this->afterTransition($transitionDocument->handle(
            document: $this->document,
            actor: auth()->user(),
            action: DocumentMovementType::Attended,
            status: DocumentStatus::Attended,
            attributes: [
                'attended_at' => now(),
            ],
            notes: $this->process_notes !== '' ? $this->process_notes : 'Documento atendido.',
            attachments: $this->movementAttachments,
        ));

        $this->process_notes = '';
        $this->movementAttachments = [];
        $this->closeActionModal();
        $this->successToast('Documento atendido correctamente.');
    }

    public function archiveDocument(TransitionDocument $transitionDocument, DocumentWorkflowActions $workflowActions): void
    {
        abort_unless(in_array('archive', $workflowActions->available($this->document, auth()->user()), true), 403);
        $this->validateMovementAttachments();

        $this->afterTransition($transitionDocument->handle(
            document: $this->document,
            actor: auth()->user(),
            action: DocumentMovementType::Archived,
            status: DocumentStatus::Archived,
            attributes: [
                'archived_at' => now(),
            ],
            notes: $this->archive_notes !== '' ? $this->archive_notes : 'Documento archivado.',
            attachments: $this->movementAttachments,
        ));

        $this->archive_notes = '';
        $this->movementAttachments = [];
        $this->closeActionModal();
        $this->successToast('Documento archivado correctamente.');
    }

    public function reopenDocument(TransitionDocument $transitionDocument, DocumentWorkflowActions $workflowActions): void
    {
        abort_unless(in_array('reopen', $workflowActions->available($this->document, auth()->user()), true), 403);
        $this->validateMovementAttachments();

        $this->afterTransition($transitionDocument->handle(
            document: $this->document,
            actor: auth()->user(),
            action: DocumentMovementType::Reopened,
            status: DocumentStatus::Received,
            notes: $this->reopen_notes !== '' ? $this->reopen_notes : 'Documento reabierto para seguimiento.',
            attachments: $this->movementAttachments,
        ));

        $this->reopen_notes = '';
        $this->movementAttachments = [];
        $this->closeActionModal();
        $this->successToast('Documento reabierto correctamente.');
    }

    public function cancelDocument(TransitionDocument $transitionDocument, DocumentWorkflowActions $workflowActions): void
    {
        abort_unless(in_array('cancel', $workflowActions->available($this->document, auth()->user()), true), 403);
        $this->validateMovementAttachments();

        $validated = $this->validateWithToastFeedback([
            'annulment_reason' => ['required', 'string'],
        ], [], [
            'annulment_reason' => 'motivo de anulacion',
        ]);

        $this->afterTransition($transitionDocument->handle(
            document: $this->document,
            actor: auth()->user(),
            action: DocumentMovementType::Cancelled,
            status: DocumentStatus::Cancelled,
            attributes: [
                'cancelled_at' => now(),
                'annulment_reason' => $validated['annulment_reason'],
            ],
            notes: $validated['annulment_reason'],
            attachments: $this->movementAttachments,
        ));

        $this->annulment_reason = '';
        $this->movementAttachments = [];
        $this->closeActionModal();
        $this->warningToast('Documento anulado correctamente.');
    }

    public function closeDocument(TransitionDocument $transitionDocument, DocumentWorkflowActions $workflowActions): void
    {
        abort_unless(in_array('close', $workflowActions->available($this->document, auth()->user()), true), 403);
        $this->validateMovementAttachments();

        $this->afterTransition($transitionDocument->handle(
            document: $this->document,
            actor: auth()->user(),
            action: DocumentMovementType::Closed,
            status: DocumentStatus::Closed,
            notes: $this->close_notes ?: 'Documento cerrado.',
            attachments: $this->movementAttachments,
        ));

        $this->close_notes = '';
        $this->movementAttachments = [];
        $this->closeActionModal();
        $this->successToast('Documento cerrado correctamente.');
    }

    public function uploadAttachments(UserAuditLogger $userAuditLogger): void
    {
        abort_unless(auth()->user()->can('documents.editar'), 403);

        $this->validateWithToastFeedback([
            'newAttachments.*' => ['required', 'file', 'max:10240'],
        ], [], [
            'newAttachments.*' => 'adjunto',
        ]);

        foreach ($this->newAttachments as $uploadedFile) {
            $contents = file_get_contents($uploadedFile->getRealPath());

            if ($contents === false) {
                continue;
            }

            $this->document
                ->addMediaFromString($contents)
                ->usingFileName($uploadedFile->getClientOriginalName())
                ->usingName(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME))
                ->toMediaCollection('attachments', 'public');
        }

        $userAuditLogger->log(
            action: 'archivo_subido',
            module: 'Documentos',
            auditable: $this->document,
            newValues: ['archivos' => collect($this->newAttachments)->map->getClientOriginalName()->all()],
            observation: 'Carga de adjuntos adicionales en documento.',
        );

        $this->newAttachments = [];
        $this->document->load($this->documentRelations);
        $this->successToast('Adjuntos cargados correctamente.');
    }

    protected function refreshDocument(): void
    {
        $this->document->refresh();
        $this->document->load($this->documentRelations);
    }

    protected function afterTransition(Document $document): Document
    {
        $this->document = $document->load($this->documentRelations);

        return $this->document;
    }

    protected function refreshExpiredDocument(): void
    {
        if (
            $this->document->due_date !== null
            && $this->document->due_date->isPast()
            && ! in_array($this->document->status, [
                DocumentStatus::Attended->value(),
                DocumentStatus::Approved->value(),
                DocumentStatus::Rejected->value(),
                DocumentStatus::Archived->value(),
                DocumentStatus::Cancelled->value(),
                DocumentStatus::Closed->value(),
                DocumentStatus::Expired->value(),
            ], true)
        ) {
            $this->document->update([
                'status' => DocumentStatus::Expired->value(),
            ]);
        }
    }

    protected function validateMovementAttachments(): void
    {
        if ($this->movementAttachments === []) {
            return;
        }

        $this->validate([
            'movementAttachments.*' => ['required', 'file', 'max:10240'],
        ]);
    }
}
