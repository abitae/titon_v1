<?php

namespace App\Livewire\Purchases;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Actions\Purchases\SyncPurchaseRequestItems;
use App\Concerns\AssignsOperationalCode;
use App\Concerns\InteractsWithToast;
use App\Enums\CorrelativeSubject;
use App\Enums\DocumentPriority;
use App\Enums\RequirementStatus;
use App\Models\CostType;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Support\DefaultDate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ManagePurchaseRequests extends Component
{
    use AssignsOperationalCode, InteractsWithToast, WithPagination;

    public string $title = 'Requerimientos';

    public string $search = '';

    public string $statusFilter = '';

    public string $projectFilter = '';

    public bool $showFormModal = false;

    public bool $showItemModal = false;

    public ?int $editingPurchaseRequestId = null;

    public ?int $editingItemIndex = null;

    public string $code = '';

    public ?int $work_project_id = null;

    public ?int $requested_by = null;

    public string $priority = 'media';

    public string $request_date = '';

    public string $description = '';

    public string $status = 'borrador';

    public string $cost_type_id = '';

    public string $item_product_or_service = '';

    public string $item_unit = 'und';

    public string $item_quantity = '1';

    public string $item_cost_center_ua = '';

    public string $item_technical_specification = '';

    public string $item_observation = '';

    /**
     * @var list<array<string, mixed>>
     */
    public array $items = [];

    public function mount(): void
    {
        $this->request_date = DefaultDate::today();
        $this->requested_by = auth()->id();
    }

    public function render(): View
    {
        $purchaseRequests = PurchaseRequest::query()
            ->with(['project', 'requester', 'costType', 'comparison.selectedQuotation.supplier'])
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
            'costTypes' => CostType::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'statusOptions' => RequirementStatus::cases(),
            'priorityOptions' => DocumentPriority::cases(),
            'summary' => [
                'total' => PurchaseRequest::query()->count(),
                'draft' => PurchaseRequest::query()->where('status', RequirementStatus::Draft->value())->count(),
                'in_process' => PurchaseRequest::query()->where('status', RequirementStatus::InProcess->value())->count(),
                'attended' => PurchaseRequest::query()->where('status', RequirementStatus::Attended->value())->count(),
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
        $this->cost_type_id = $purchaseRequest->cost_type_id ? (string) $purchaseRequest->cost_type_id : '';
        $this->items = $purchaseRequest->items->map(fn ($item): array => $this->itemFromModel($item))->all();
        $this->showFormModal = true;
    }

    public function openItemModal(?int $index = null): void
    {
        $this->resetItemDraft();
        $this->editingItemIndex = $index;

        if ($index !== null && isset($this->items[$index])) {
            $item = $this->items[$index];
            $this->item_product_or_service = $item['product_or_service'];
            $this->item_unit = $item['unit'];
            $this->item_quantity = (string) $item['quantity'];
            $this->item_cost_center_ua = $item['cost_center_ua'] ?? '';
            $this->item_technical_specification = $item['technical_specification'] ?? '';
            $this->item_observation = $item['observation'] ?? '';
        }

        $this->showItemModal = true;
    }

    public function saveItem(): void
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        abort_if($company === null, 403);

        $validated = $this->validate([
            'item_product_or_service' => ['required', 'string', 'max:255'],
            'item_unit' => ['required', 'string', 'max:50'],
            'item_quantity' => ['required', 'numeric', 'min:0.01'],
            'item_cost_center_ua' => ['nullable', 'string', 'max:150'],
            'item_technical_specification' => ['nullable', 'string'],
            'item_observation' => ['nullable', 'string'],
        ], [], [
            'item_product_or_service' => 'producto o servicio',
            'item_unit' => 'unidad',
            'item_quantity' => 'cantidad',
            'item_cost_center_ua' => 'centro de costo UA',
            'item_technical_specification' => 'especificacion tecnica',
            'item_observation' => 'observacion',
        ]);

        $item = [
            'product_or_service' => $validated['item_product_or_service'],
            'description' => $validated['item_product_or_service'],
            'unit' => $validated['item_unit'],
            'quantity' => (string) $validated['item_quantity'],
            'cost_center_ua' => $validated['item_cost_center_ua'] ?? '',
            'technical_specification' => $validated['item_technical_specification'] ?? '',
            'observation' => $validated['item_observation'] ?? '',
        ];

        if ($this->editingItemIndex !== null) {
            $this->items[$this->editingItemIndex] = $item;
        } else {
            $this->items[] = $item;
        }

        $this->closeItemModal();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function savePurchaseRequest(SyncPurchaseRequestItems $syncPurchaseRequestItems): void
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        abort_if($company === null, 403);
        abort_unless(auth()->user()->can($this->editingPurchaseRequestId ? 'purchases.editar' : 'purchases.crear'), 403);

        $validated = $this->validate([
            'work_project_id' => ['required', 'integer', 'exists:projects,id'],
            'requested_by' => ['required', 'integer', 'exists:users,id'],
            'priority' => ['required', Rule::in(DocumentPriority::values())],
            'request_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(RequirementStatus::values())],
            'cost_type_id' => [
                'nullable',
                Rule::exists('cost_types', 'id')->where(fn ($query) => $query->where('company_id', $company->id)),
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_or_service' => ['required', 'string', 'max:255'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.cost_center_ua' => ['nullable', 'string', 'max:150'],
            'items.*.technical_specification' => ['nullable', 'string'],
            'items.*.observation' => ['nullable', 'string'],
        ], [], $this->purchaseRequestValidationAttributes());
        $isEditing = $this->editingPurchaseRequestId !== null;

        $purchaseRequest = DB::transaction(function () use ($validated, $company, $isEditing): PurchaseRequest {
            $project = Project::query()->findOrFail($validated['work_project_id']);
            $existing = $isEditing
                ? PurchaseRequest::query()->find($this->editingPurchaseRequestId)
                : null;

            $finalCode = $this->assignOperationalCode(
                $company,
                CorrelativeSubject::Requirement,
                $project,
                existingCode: $existing?->code,
                isEditing: $isEditing,
            );

            return PurchaseRequest::query()->updateOrCreate(
                ['id' => $this->editingPurchaseRequestId],
                [
                    'company_id' => $company->id,
                    'work_project_id' => $validated['work_project_id'],
                    'responsible_user_id' => $validated['requested_by'],
                    'requested_by' => $validated['requested_by'],
                    'code' => $finalCode,
                    'priority' => $validated['priority'],
                    'request_date' => $validated['request_date'],
                    'description' => $validated['description'] ?? null,
                    'status' => $validated['status'],
                    'cost_type_id' => filled($validated['cost_type_id'] ?? null) ? (int) $validated['cost_type_id'] : null,
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
        $this->closeItemModal();
    }

    public function closeItemModal(): void
    {
        $this->showItemModal = false;
        $this->editingItemIndex = null;
        $this->resetItemDraft();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingPurchaseRequestId',
            'code',
            'work_project_id',
            'description',
            'cost_type_id',
            'items',
        ]);

        $this->requested_by = auth()->id();
        $this->priority = DocumentPriority::Medium->value();
        $this->request_date = DefaultDate::today();
        $this->status = RequirementStatus::Draft->value();
        $this->showFormModal = false;
        $this->closeItemModal();
    }

    protected function resetItemDraft(): void
    {
        $this->reset([
            'item_product_or_service',
            'item_cost_center_ua',
            'item_technical_specification',
            'item_observation',
        ]);

        $this->item_unit = 'und';
        $this->item_quantity = '1';
    }

    /**
     * @return array<string, mixed>
     */
    protected function itemFromModel(object $item): array
    {
        return [
            'product_or_service' => $item->description,
            'description' => $item->description,
            'unit' => $item->unit,
            'quantity' => (string) $item->quantity,
            'cost_center_ua' => $item->cost_center_ua ?? '',
            'technical_specification' => $item->technical_specification ?? '',
            'observation' => $item->observation ?? '',
        ];
    }

    protected function companyUsers()
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        return $company?->users()->wherePivot('active', true)->orderBy('name')->get() ?? collect();
    }

    /**
     * @return array<string, string>
     */
    protected function purchaseRequestValidationAttributes(): array
    {
        return [
            'work_project_id' => 'obra / proyecto',
            'requested_by' => 'solicitante',
            'priority' => 'prioridad',
            'request_date' => 'fecha de solicitud',
            'description' => 'descripción',
            'status' => 'estado',
            'cost_type_id' => 'tipo de costo',
            'items' => 'ítems',
            'items.*.product_or_service' => 'producto o servicio',
            'items.*.unit' => 'unidad',
            'items.*.quantity' => 'cantidad',
            'items.*.cost_center_ua' => 'centro de costo UA',
            'items.*.technical_specification' => 'especificación técnica',
            'items.*.observation' => 'observación',
        ];
    }
}
