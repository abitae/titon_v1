<?php

namespace App\Livewire\Mechanics;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Actions\Mechanics\RecordFleetSparePartMovement;
use App\Concerns\InteractsWithToast;
use App\Enums\CorrelativeSubject;
use App\Enums\FleetSparePartMovementDirection;
use App\Enums\FleetSparePartStatus;
use App\Models\FleetSparePart;
use App\Models\FleetWorkOrder;
use App\Models\Project;
use App\Models\Supplier;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ManageFleetSpareParts extends Component
{
    use InteractsWithToast, WithPagination;

    public string $title = 'Inventario de repuestos';

    public string $search = '';

    public bool $showPartModal = false;

    public bool $showMovementModal = false;

    public ?int $editingPartId = null;

    public ?int $movement_spare_part_id = null;

    public string $movement_direction = '';

    public string $movement_quantity = '1';

    public string $movement_unit_cost = '';

    public ?int $movement_work_order_id = null;

    public string $movement_reference = '';

    public string $code = '';

    public string $name = '';

    public string $category = '';

    public string $unit = 'und';

    public string $stock_quantity = '0';

    public string $min_stock = '0';

    public string $unit_cost = '0';

    public ?int $supplier_id = null;

    public ?int $warehouse_project_id = null;

    public string $status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('mecanica.ver'), 403);
        $this->status = FleetSparePartStatus::Active->value();
        $this->movement_direction = FleetSparePartMovementDirection::Inbound->value();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.mechanics.manage-fleet-spare-parts', [
            'parts' => FleetSparePart::query()
                ->with(['supplier', 'warehouseProject', 'movements' => fn ($query) => $query->latest()->limit(5)])
                ->when($this->search !== '', fn ($query) => $query->where(function ($query): void {
                    $query->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%')
                        ->orWhere('category', 'like', '%'.$this->search.'%');
                }))
                ->orderBy('code')
                ->paginate(12),
            'suppliers' => Supplier::query()->orderBy('business_name')->get(['id', 'business_name']),
            'projects' => Project::query()->select(['id', 'code', 'name'])->orderBy('code')->get(),
            'workOrders' => FleetWorkOrder::query()->orderByDesc('id')->limit(100)->get(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function openPartCreate(): void
    {
        abort_unless(auth()->user()?->can('mecanica.crear'), 403);
        $this->resetPartForm();
        $this->showPartModal = true;
    }

    public function openPartEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('mecanica.editar'), 403);
        $row = FleetSparePart::query()->findOrFail($id);
        $this->editingPartId = $row->id;
        $this->code = $row->code;
        $this->name = $row->name;
        $this->category = (string) ($row->category ?? '');
        $this->unit = $row->unit;
        $this->stock_quantity = (string) $row->stock_quantity;
        $this->min_stock = (string) $row->min_stock;
        $this->unit_cost = (string) $row->unit_cost;
        $this->supplier_id = $row->supplier_id;
        $this->warehouse_project_id = $row->warehouse_project_id;
        $this->status = $row->status;
        $this->showPartModal = true;
    }

    public function savePart(ResolveCurrentCompany $resolveCurrentCompany): void
    {
        abort_unless(auth()->user()->can($this->editingPartId ? 'mecanica.editar' : 'mecanica.crear'), 403);
        $company = $resolveCurrentCompany->handle(auth()->user());
        abort_if($company === null, 403);

        $validated = $this->validate([
            'code' => [
                Rule::requiredIf($this->editingPartId !== null),
                'nullable',
                'string',
                'max:50',
                Rule::unique('fleet_spare_parts', 'code')->where(fn ($q) => $q->where('company_id', $company->id))->ignore($this->editingPartId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:120'],
            'unit' => ['required', 'string', 'max:32'],
            'stock_quantity' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', Rule::exists('suppliers', 'id')->where(fn ($q) => $q->where('company_id', $company->id))],
            'warehouse_project_id' => ['nullable', Rule::exists('projects', 'id')->where(fn ($q) => $q->where('company_id', $company->id))],
            'status' => ['required', Rule::in([FleetSparePartStatus::Active->value(), FleetSparePartStatus::Inactive->value()])],
        ]);

        $payload = [
            'company_id' => $company->id,
            ...$validated,
            'supplier_id' => $validated['supplier_id'],
            'warehouse_project_id' => $validated['warehouse_project_id'],
            'category' => $validated['category'] ?: null,
        ];

        if ($this->editingPartId) {
            $part = FleetSparePart::query()->findOrFail($this->editingPartId);
            unset($payload['company_id']);
            $part->update($payload);
        } else {
            DB::transaction(function () use ($payload, $company, $validated): void {
                $finalCode = trim((string) ($validated['code'] ?? ''));

                if ($finalCode === '') {
                    $finalCode = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::FleetSparePart);
                }

                FleetSparePart::query()->create([
                    ...$payload,
                    'code' => $finalCode,
                ]);
            });
        }

        $this->resetPartForm();
        $this->successToast('Repuesto guardado.');
    }

    public function openMovement(int $partId): void
    {
        abort_unless(auth()->user()?->can('mecanica.crear'), 403);
        $this->movement_spare_part_id = $partId;
        $part = FleetSparePart::query()->findOrFail($partId);
        $this->movement_unit_cost = (string) $part->unit_cost;
        $this->movement_quantity = '1';
        $this->movement_work_order_id = null;
        $this->movement_reference = '';
        $this->movement_direction = FleetSparePartMovementDirection::Inbound->value();
        $this->showMovementModal = true;
    }

    public function saveMovement(RecordFleetSparePartMovement $recordFleetSparePartMovement): void
    {
        abort_unless(auth()->user()?->can('mecanica.crear'), 403);

        $part = FleetSparePart::query()->findOrFail($this->movement_spare_part_id);

        $validated = $this->validate([
            'movement_direction' => ['required', Rule::in([FleetSparePartMovementDirection::Inbound->value(), FleetSparePartMovementDirection::Outbound->value()])],
            'movement_quantity' => ['required', 'numeric', 'min:0.001'],
            'movement_unit_cost' => ['nullable', 'numeric', 'min:0'],
            'movement_work_order_id' => ['nullable', 'exists:fleet_work_orders,id'],
            'movement_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $payload = [
            'direction' => $validated['movement_direction'],
            'quantity' => $validated['movement_quantity'],
            'unit_cost' => $validated['movement_unit_cost'] ?: null,
            'fleet_work_order_id' => $validated['movement_work_order_id'],
            'reference' => $validated['movement_reference'] ?: null,
        ];

        try {
            $recordFleetSparePartMovement->handle($part, auth()->user(), $payload);
            $this->showMovementModal = false;
            $this->successToast('Movimiento registrado.');
        } catch (\Throwable $exception) {
            $this->dangerToast($exception->getMessage());
        }
    }

    public function deletePart(int $id): void
    {
        abort_unless(auth()->user()?->can('mecanica.eliminar'), 403);
        FleetSparePart::query()->findOrFail($id)->delete();
        $this->warningToast('Repuesto eliminado.');
    }

    public function closePart(): void
    {
        $this->showPartModal = false;
    }

    public function closeMovement(): void
    {
        $this->showMovementModal = false;
    }

    protected function resetPartForm(): void
    {
        $this->reset([
            'editingPartId',
            'code',
            'name',
            'category',
            'supplier_id',
            'warehouse_project_id',
        ]);
        $this->unit = 'und';
        $this->stock_quantity = '0';
        $this->min_stock = '0';
        $this->unit_cost = '0';
        $this->status = FleetSparePartStatus::Active->value();
        $this->showPartModal = false;
    }
}
