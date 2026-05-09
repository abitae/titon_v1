<?php

namespace App\Livewire\Purchases;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Actions\Purchases\SyncPurchaseRequestItems;
use App\Concerns\InteractsWithToast;
use App\Enums\CorrelativeSubject;
use App\Enums\DocumentPriority;
use App\Enums\PurchaseRequestStatus;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ManagePurchaseRequests extends Component
{
    use InteractsWithToast, WithPagination;

    public string $title = 'Solicitudes de compra';

    public string $search = '';

    public string $statusFilter = '';

    public string $projectFilter = '';

    public bool $showFormModal = false;

    public ?int $editingPurchaseRequestId = null;

    public string $code = '';

    public ?int $work_project_id = null;

    public ?int $requested_by = null;

    public string $priority = 'media';

    public string $request_date = '';

    public string $description = '';

    public string $status = 'borrador';

    /**
     * @var list<array<string, mixed>>
     */
    public array $items = [];

    public function mount(): void
    {
        $this->request_date = now()->toDateString();
        $this->requested_by = auth()->id();
        $this->items = [$this->emptyItem()];
    }

    public function render(): View
    {
        $purchaseRequests = PurchaseRequest::query()
            ->with(['project', 'requester', 'comparison.selectedQuotation.supplier'])
            ->withCount(['items', 'quotations'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery
                        ->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->projectFilter !== '', fn ($query) => $query->where('work_project_id', $this->projectFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.purchases.manage-purchase-requests', [
            'purchaseRequests' => $purchaseRequests,
            'projects' => Project::query()->orderBy('name')->get(),
            'users' => $this->companyUsers(),
            'statusOptions' => PurchaseRequestStatus::cases(),
            'priorityOptions' => DocumentPriority::cases(),
            'summary' => [
                'total' => PurchaseRequest::query()->count(),
                'open' => PurchaseRequest::query()->whereNotIn('status', [
                    PurchaseRequestStatus::Closed->value(),
                    PurchaseRequestStatus::Ordered->value(),
                ])->count(),
                'awarded' => PurchaseRequest::query()->where('status', PurchaseRequestStatus::Awarded->value())->count(),
            ],
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

    public function updatedProjectFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $purchaseRequestId): void
    {
        $purchaseRequest = PurchaseRequest::query()->with('items')->findOrFail($purchaseRequestId);

        $this->editingPurchaseRequestId = $purchaseRequest->id;
        $this->code = $purchaseRequest->code;
        $this->work_project_id = $purchaseRequest->work_project_id;
        $this->requested_by = $purchaseRequest->requested_by;
        $this->priority = $purchaseRequest->priority;
        $this->request_date = $purchaseRequest->request_date?->format('Y-m-d') ?? '';
        $this->description = $purchaseRequest->description ?? '';
        $this->status = $purchaseRequest->status;
        $this->items = $purchaseRequest->items->map(fn ($item): array => [
            'product_or_service' => $item->product_or_service,
            'unit' => $item->unit,
            'quantity' => (string) $item->quantity,
            'technical_specification' => $item->technical_specification ?? '',
            'observation' => $item->observation ?? '',
        ])->all();
        $this->showFormModal = true;
    }

    public function addItem(): void
    {
        $this->items[] = $this->emptyItem();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if ($this->items === []) {
            $this->items[] = $this->emptyItem();
        }
    }

    public function savePurchaseRequest(SyncPurchaseRequestItems $syncPurchaseRequestItems): void
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        abort_if($company === null, 403);
        abort_unless(auth()->user()->can($this->editingPurchaseRequestId ? 'purchases.editar' : 'purchases.crear'), 403);

        $validated = $this->validate([
            'code' => [
                Rule::requiredIf($this->editingPurchaseRequestId !== null),
                'nullable',
                'string',
                'max:50',
                Rule::unique('purchase_requests', 'code')
                    ->where(fn ($query) => $query->where('company_id', $company->id))
                    ->ignore($this->editingPurchaseRequestId),
            ],
            'work_project_id' => ['required', 'integer', 'exists:projects,id'],
            'requested_by' => ['required', 'integer', 'exists:users,id'],
            'priority' => ['required', Rule::in(DocumentPriority::values())],
            'request_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(PurchaseRequestStatus::values())],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_or_service' => ['required', 'string', 'max:255'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.technical_specification' => ['nullable', 'string'],
            'items.*.observation' => ['nullable', 'string'],
        ]);
        $isEditing = $this->editingPurchaseRequestId !== null;

        $purchaseRequest = DB::transaction(function () use ($validated, $company, $isEditing): PurchaseRequest {
            $finalCode = trim((string) ($validated['code'] ?? ''));

            if (! $isEditing && $finalCode === '') {
                $finalCode = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::PurchaseRequest);
            }

            return PurchaseRequest::query()->updateOrCreate(
                ['id' => $this->editingPurchaseRequestId],
                [
                    'company_id' => $company->id,
                    'work_project_id' => $validated['work_project_id'],
                    'requested_by' => $validated['requested_by'],
                    'code' => $isEditing ? $validated['code'] : $finalCode,
                    'priority' => $validated['priority'],
                    'request_date' => $validated['request_date'],
                    'description' => $validated['description'] ?? null,
                    'status' => $validated['status'],
                ],
            );
        });

        $syncPurchaseRequestItems->handle($purchaseRequest, $validated['items']);

        $this->resetForm();
        $this->successToast($isEditing ? 'Solicitud de compra actualizada correctamente.' : 'Solicitud de compra creada correctamente.');
    }

    public function deletePurchaseRequest(int $purchaseRequestId): void
    {
        abort_unless(auth()->user()->can('purchases.eliminar'), 403);

        PurchaseRequest::query()->findOrFail($purchaseRequestId)->delete();
        $this->resetPage();
        $this->warningToast('Solicitud de compra eliminada correctamente.');
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingPurchaseRequestId',
            'code',
            'work_project_id',
            'description',
        ]);

        $this->requested_by = auth()->id();
        $this->priority = DocumentPriority::Medium->value();
        $this->request_date = now()->toDateString();
        $this->status = PurchaseRequestStatus::Draft->value();
        $this->items = [$this->emptyItem()];
        $this->showFormModal = false;
    }

    /**
     * @return array<string, string>
     */
    protected function emptyItem(): array
    {
        return [
            'product_or_service' => '',
            'unit' => 'und',
            'quantity' => '1',
            'technical_specification' => '',
            'observation' => '',
        ];
    }

    protected function companyUsers()
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        return $company?->users()->wherePivot('active', true)->orderBy('name')->get() ?? collect();
    }
}
