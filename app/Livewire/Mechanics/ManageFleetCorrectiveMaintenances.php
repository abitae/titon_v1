<?php

namespace App\Livewire\Mechanics;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithFleetEquipmentSearch;
use App\Concerns\InteractsWithToast;
use App\Enums\CorrelativeSubject;
use App\Enums\FleetCorrectiveMaintenanceStatus;
use App\Models\FleetCorrectiveMaintenance;
use App\Models\FleetEquipment;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use App\Support\DefaultDate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageFleetCorrectiveMaintenances extends Component
{
    use InteractsWithFleetEquipmentSearch, InteractsWithToast, WithFileUploads, WithPagination;

    public string $title = 'Mantenimiento correctivo';

    public string $search = '';

    public string $statusFilter = '';

    public bool $showFormModal = false;

    public ?int $editingId = null;

    public ?int $fleet_equipment_id = null;

    public string $failure_at = '';

    public string $failure_description = '';

    public string $diagnosis = '';

    public string $supplier_workshop = '';

    public string $estimated_cost = '';

    public string $real_cost = '';

    public string $status = '';

    public string $observations = '';

    public ?int $responsible_user_id = null;

    public array $failure_photos = [];

    public array $documents = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.ver'), 403);
        $this->status = FleetCorrectiveMaintenanceStatus::Reported->value();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.mechanics.manage-fleet-corrective-maintenances', [
            'rows' => FleetCorrectiveMaintenance::query()
                ->with(['equipment', 'responsibleUser'])
                ->when($this->search !== '', fn ($query) => $query->where(function ($query): void {
                    $query->where('failure_description', 'like', '%'.$this->search.'%')
                        ->orWhere('supplier_workshop', 'like', '%'.$this->search.'%')
                        ->orWhereHas('equipment', fn ($equipment) => $equipment
                            ->where('internal_code', 'like', '%'.$this->search.'%')
                            ->orWhere('name', 'like', '%'.$this->search.'%'));
                }))
                ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
                ->latest('failure_at')
                ->paginate(12),
            'equipmentOptions' => $this->fleetEquipmentSelectOptions(),
            'responsibleUsers' => $this->responsibleUsers(),
            'statuses' => FleetCorrectiveMaintenanceStatus::cases(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.crear'), 403);
        $this->resetForm();
        $this->fleet_equipment_id = FleetEquipment::query()->orderBy('internal_code')->value('id');
        if ($this->fleet_equipment_id === null) {
            $this->dangerToast('Registre primero un equipo.');

            return;
        }

        $this->failure_at = DefaultDate::nowDateTimeLocal();
        $this->syncFleetEquipmentSearch();
        $this->showFormModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.crear'), 403);
        $row = FleetCorrectiveMaintenance::query()->findOrFail($id);
        $this->editingId = $row->id;
        $this->fleet_equipment_id = $row->fleet_equipment_id;
        $this->failure_at = $row->failure_at->format('Y-m-d\TH:i');
        $this->failure_description = $row->failure_description;
        $this->diagnosis = (string) ($row->diagnosis ?? '');
        $this->supplier_workshop = (string) ($row->supplier_workshop ?? '');
        $this->estimated_cost = (string) ($row->estimated_cost ?? '');
        $this->real_cost = (string) ($row->real_cost ?? '');
        $this->status = $row->status;
        $this->observations = (string) ($row->observations ?? '');
        $this->responsible_user_id = $row->responsible_user_id;
        $this->failure_photos = [];
        $this->documents = [];
        $this->syncFleetEquipmentSearch();
        $this->showFormModal = true;
    }

    public function save(ResolveCurrentCompany $resolveCurrentCompany): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.crear'), 403);
        $company = $resolveCurrentCompany->handle(auth()->user());
        abort_if($company === null, 403);

        $validated = $this->validate([
            'fleet_equipment_id' => ['required', Rule::exists('fleet_equipments', 'id')->where(fn ($q) => $q->where('company_id', $company->id))],
            'failure_at' => ['required', 'date'],
            'failure_description' => ['required', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'supplier_workshop' => ['nullable', 'string', 'max:255'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'real_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(array_map(fn ($s) => $s->value(), FleetCorrectiveMaintenanceStatus::cases()))],
            'observations' => ['nullable', 'string'],
            'responsible_user_id' => ['nullable', 'exists:users,id'],
            'failure_photos.*' => ['nullable', 'file', 'max:10240'],
            'documents.*' => ['nullable', 'file', 'max:10240'],
        ]);

        $payload = [
            'company_id' => $company->id,
            'fleet_equipment_id' => $validated['fleet_equipment_id'],
            'failure_at' => $validated['failure_at'],
            'failure_description' => $validated['failure_description'],
            'diagnosis' => $validated['diagnosis'] ?: null,
            'supplier_workshop' => $validated['supplier_workshop'] ?: null,
            'estimated_cost' => $validated['estimated_cost'] ?? null,
            'real_cost' => $validated['real_cost'] ?? null,
            'status' => $validated['status'],
            'observations' => $validated['observations'] ?: null,
            'responsible_user_id' => $validated['responsible_user_id'],
        ];

        if ($this->editingId) {
            $model = FleetCorrectiveMaintenance::query()->findOrFail($this->editingId);
            $model->update($payload);
        } else {
            $model = DB::transaction(function () use ($payload, $company): FleetCorrectiveMaintenance {
                $payload['code'] = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::FleetCorrectiveMaintenance);

                return FleetCorrectiveMaintenance::query()->create($payload);
            });
        }

        foreach ($this->failure_photos as $uploadedFile) {
            $model->addMedia($uploadedFile->getRealPath())
                ->usingFileName($uploadedFile->getClientOriginalName())
                ->toMediaCollection('failure_photos', 'public');
        }

        foreach ($this->documents as $uploadedFile) {
            $model->addMedia($uploadedFile->getRealPath())
                ->usingFileName($uploadedFile->getClientOriginalName())
                ->toMediaCollection('corrective_documents', 'public');
        }

        $this->resetForm();
        $this->successToast('Correctivo guardado.');
    }

    public function deleteRow(int $id): void
    {
        abort_unless(auth()->user()?->can('mecanica.eliminar'), 403);
        FleetCorrectiveMaintenance::query()->findOrFail($id)->delete();
        $this->warningToast('Eliminado.');
    }

    public function close(): void
    {
        $this->showFormModal = false;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingId',
            'fleet_equipment_id',
            'failure_at',
            'failure_description',
            'diagnosis',
            'supplier_workshop',
            'estimated_cost',
            'real_cost',
            'observations',
            'responsible_user_id',
            'failure_photos',
            'documents',
        ]);
        $this->status = FleetCorrectiveMaintenanceStatus::Reported->value();
        $this->failure_at = DefaultDate::nowDateTimeLocal();
        $this->resetFleetEquipmentSearch();
        $this->showFormModal = false;
    }

    protected function responsibleUsers(): Collection
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        return $company?->users()->wherePivot('active', true)->orderBy('name')->get() ?? collect();
    }
}
