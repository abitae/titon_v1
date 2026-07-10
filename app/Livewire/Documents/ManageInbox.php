<?php

namespace App\Livewire\Documents;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Actions\Documents\RecordDocumentMovement;
use App\Concerns\InteractsWithToast;
use App\Enums\CatalogType;
use App\Enums\CorrelativeSubject;
use App\Enums\DocumentMovementType;
use App\Enums\DocumentPriority;
use App\Enums\DocumentStatus;
use App\Models\CatalogItem;
use App\Models\Document;
use App\Models\Project;
use App\Services\Audit\UserAuditLogger;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use App\Services\Documents\GenerateDocumentCode;
use App\Support\DefaultDate;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageInbox extends Component
{
    use InteractsWithToast, WithFileUploads, WithPagination;

    public string $title = 'Bandeja de entrada';

    public string $search = '';

    public string $statusFilter = '';

    public string $priorityFilter = '';

    public string $projectFilter = '';

    public bool $showCreateModal = false;

    public array $attachments = [];

    public string $code = '';

    public string $document_number = '';

    public ?int $work_project_id = null;

    public ?int $document_type_id = null;

    public string $subject = '';

    public string $description = '';

    public ?int $origin_area_id = null;

    public ?int $destination_area_id = null;

    public ?int $current_user_id = null;

    public string $priority = 'media';

    public string $issue_date = '';

    public string $reception_date = '';

    public string $due_date = '';

    public string $observations = '';

    public function mount(GenerateDocumentCode $generateDocumentCode): void
    {
        abort_unless(auth()->user()?->can('documents.ver'), 403);

        $this->issue_date = DefaultDate::today();
        $this->reception_date = DefaultDate::today();
        $this->due_date = DefaultDate::daysAhead(30);
        $this->current_user_id = auth()->id();
        $this->refreshExpiredDocuments();

        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        if ($company !== null) {
            $this->code = $generateDocumentCode->handle($company->id);
        }
    }

    public function render(): View
    {
        $documents = $this->inboxQuery()
            ->with(['project', 'documentType', 'originArea', 'destinationArea', 'currentUser', 'createdByUser'])
            ->latest()
            ->paginate(10);

        return view('livewire.documents.manage-inbox', [
            'documents' => $documents,
            'summary' => $this->inboxSummary(),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'documentTypes' => CatalogItem::query()->ofType(CatalogType::DocumentType)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'areas' => CatalogItem::query()->ofType(CatalogType::Area)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'users' => $this->companyUsers(),
            'statusOptions' => DocumentStatus::cases(),
            'priorityOptions' => DocumentPriority::cases(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedProjectFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(GenerateDocumentCode $generateDocumentCode): void
    {
        abort_unless(auth()->user()->can('documents.crear'), 403);

        $this->resetForm();
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        if ($company !== null) {
            $this->code = $generateDocumentCode->handle($company->id);
        }

        $this->showCreateModal = true;
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
    }

    public function saveDocument(RecordDocumentMovement $recordDocumentMovement, UserAuditLogger $userAuditLogger): void
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        abort_if($company === null, 403);
        abort_unless(auth()->user()->can('documents.crear'), 403);

        $validated = $this->validateWithToastFeedback([
            'document_number' => ['nullable', 'string', 'max:100'],
            'work_project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'document_type_id' => ['nullable', 'integer', 'exists:catalog_items,id'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'origin_area_id' => ['nullable', 'integer', 'exists:catalog_items,id'],
            'destination_area_id' => ['nullable', 'integer', 'exists:catalog_items,id'],
            'current_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'priority' => ['required', Rule::in(DocumentPriority::values())],
            'issue_date' => ['nullable', 'date'],
            'reception_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'observations' => ['nullable', 'string'],
            'attachments.*' => ['nullable', 'file', 'max:10240'],
        ], [], [
            'subject' => 'asunto',
            'document_type_id' => 'tipo de documento',
            'work_project_id' => 'obra',
            'issue_date' => 'fecha de emision',
            'reception_date' => 'fecha de recepcion',
            'due_date' => 'fecha de vencimiento',
            'attachments.*' => 'adjunto',
        ]);

        $document = DB::transaction(function () use ($validated, $company, $recordDocumentMovement, $userAuditLogger): Document {
            $code = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::Document);

            $document = Document::query()->create([
                ...$validated,
                'code' => $code,
                'company_id' => $company->id,
                'created_by_user_id' => auth()->id(),
                'current_user_id' => $validated['current_user_id'] ?: auth()->id(),
                'status' => DocumentStatus::Registered->value(),
            ]);

            $this->persistInboxDocumentMediaAndMovement(
                $document,
                $recordDocumentMovement,
                $userAuditLogger,
            );

            return $document;
        });

        $this->resetForm();
        $this->flashSuccessToast('Documento registrado correctamente.');
        $this->redirectRoute('documents.show', $document);
    }

    protected function inboxQuery(): Builder
    {
        return $this->inboxBaseQuery()
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $nestedQuery): void {
                    $nestedQuery
                        ->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('subject', 'like', '%'.$this->search.'%')
                        ->orWhere('document_number', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== '', fn (Builder $query) => $query->where('status', $this->statusFilter))
            ->when($this->priorityFilter !== '', fn (Builder $query) => $query->where('priority', $this->priorityFilter))
            ->when($this->projectFilter !== '', fn (Builder $query) => $query->where('work_project_id', $this->projectFilter));
    }

    protected function inboxBaseQuery(): Builder
    {
        return Document::query()->where('current_user_id', auth()->id());
    }

    /**
     * @return array{total: int, pending: int, expired: int}
     */
    protected function inboxSummary(): array
    {
        $pendingStatuses = [
            DocumentStatus::Registered->value(),
            DocumentStatus::InProgress->value(),
            DocumentStatus::Derived->value(),
            DocumentStatus::Received->value(),
            DocumentStatus::InReview->value(),
            DocumentStatus::Observed->value(),
        ];

        $placeholders = implode(', ', array_fill(0, count($pendingStatuses), '?'));

        $summary = $this->inboxBaseQuery()
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when status in ({$placeholders}) then 1 else 0 end) as pending", $pendingStatuses)
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as expired', [DocumentStatus::Expired->value()])
            ->first();

        return [
            'total' => (int) ($summary->total ?? 0),
            'pending' => (int) ($summary->pending ?? 0),
            'expired' => (int) ($summary->expired ?? 0),
        ];
    }

    protected function persistInboxDocumentMediaAndMovement(
        Document $document,
        RecordDocumentMovement $recordDocumentMovement,
        UserAuditLogger $userAuditLogger,
    ): void {
        $movementAttachments = [];

        foreach ($this->attachments as $uploadedFile) {
            $contents = file_get_contents($uploadedFile->getRealPath());

            if ($contents === false) {
                continue;
            }

            $movementAttachments[] = [
                'name' => $uploadedFile->getClientOriginalName(),
                'contents' => $contents,
            ];

            $document
                ->addMediaFromString($contents)
                ->usingFileName($uploadedFile->getClientOriginalName())
                ->usingName(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME))
                ->toMediaCollection('attachments', 'public');
        }

        if ($this->attachments !== []) {
            $userAuditLogger->log(
                action: 'archivo_subido',
                module: 'Documentos',
                auditable: $document,
                newValues: ['archivos' => collect($this->attachments)->map->getClientOriginalName()->all()],
                observation: 'Carga de archivos en documento.',
            );
        }

        $recordDocumentMovement->handle(
            document: $document,
            user: auth()->user(),
            action: DocumentMovementType::Registered,
            toStatus: $document->status,
            notes: 'Documento registrado en la bandeja.',
            toAreaId: $document->destination_area_id,
            toUserId: $document->current_user_id,
            metadata: [
                'document_number' => $document->document_number,
                'due_date' => $document->due_date?->toDateString(),
            ],
            attachments: $movementAttachments,
        );
    }

    protected function resetForm(): void
    {
        $this->reset([
            'attachments',
            'code',
            'document_number',
            'work_project_id',
            'document_type_id',
            'subject',
            'description',
            'origin_area_id',
            'destination_area_id',
            'due_date',
            'observations',
        ]);

        $this->priority = DocumentPriority::Medium->value();
        $this->issue_date = DefaultDate::today();
        $this->reception_date = DefaultDate::today();
        $this->due_date = DefaultDate::daysAhead(30);
        $this->current_user_id = auth()->id();
        $this->showCreateModal = false;
    }

    protected function companyUsers()
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        return $company?->users()->wherePivot('active', true)->orderBy('name')->get(['users.id', 'users.name']) ?? collect();
    }

    protected function refreshExpiredDocuments(): void
    {
        Document::query()
            ->where('current_user_id', auth()->id())
            ->whereDate('due_date', '<', today())
            ->whereNotIn('status', [
                DocumentStatus::Attended->value(),
                DocumentStatus::Approved->value(),
                DocumentStatus::Rejected->value(),
                DocumentStatus::Archived->value(),
                DocumentStatus::Cancelled->value(),
                DocumentStatus::Closed->value(),
                DocumentStatus::Expired->value(),
            ])
            ->update(['status' => DocumentStatus::Expired->value()]);
    }
}
