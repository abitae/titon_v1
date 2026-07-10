<?php

namespace App\Livewire\Mechanics;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithFleetEquipmentSearch;
use App\Concerns\InteractsWithToast;
use App\Enums\CorrelativeSubject;
use App\Enums\DocumentPriority;
use App\Enums\FleetPreventiveMaintenanceStatus;
use App\Models\FleetEquipment;
use App\Models\FleetPreventiveMaintenance;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use App\Support\DefaultDate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ManageFleetPreventiveMaintenances extends Component
{
    use InteractsWithFleetEquipmentSearch, InteractsWithToast, WithPagination;

    public string $title = 'Mantenimiento preventivo';

    public string $search = '';

    public string $statusFilter = '';

    public bool $showFormModal = false;

    public ?int $editingId = null;

    public ?int $fleet_equipment_id = null;

    public string $maintenance_type = '';

    public string $scheduled_date = '';

    public string $scheduled_odometer = '';

    public string $scheduled_hour_meter = '';

    public string $priority = '';

    public string $status = '';

    public string $cost = '';

    public string $observations = '';

    public ?int $responsible_user_id = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.ver'), 403);
        $this->priority = DocumentPriority::Medium->value();
        $this->status = FleetPreventiveMaintenanceStatus::Scheduled->value();
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
        return view('livewire.mechanics.manage-fleet-preventive-maintenances', [
            'rows' => FleetPreventiveMaintenance::query()
                ->with(['equipment', 'responsibleUser'])
                ->when($this->search !== '', fn ($query) => $query->where(function ($query): void {
                    $query->where('maintenance_type', 'like', '%'.$this->search.'%')
                        ->orWhereHas('equipment', fn ($equipment) => $equipment
                            ->where('internal_code', 'like', '%'.$this->search.'%')
                            ->orWhere('name', 'like', '%'.$this->search.'%'));
                }))
                ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
                ->latest('scheduled_date')
                ->paginate(12),
            'equipmentOptions' => $this->fleetEquipmentSelectOptions(),
            'responsibleUsers' => $this->responsibleUsers(),
            'statuses' => FleetPreventiveMaintenanceStatus::cases(),
            'priorities' => DocumentPriority::cases(),
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

        $this->syncFleetEquipmentSearch();
        $this->showFormModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('mantenimientos.crear'), 403);
        $row = FleetPreventiveMaintenance::query()->findOrFail($id);
        $this->editingId = $row->id;
        $this->fleet_equipment_id = $row->fleet_equipment_id;
        $this->maintenance_type = $row->maintenance_type;
        $this->scheduled_date = $row->scheduled_date?->format('Y-m-d') ?? '';
        $this->scheduled_odometer = (string) ($row->scheduled_odometer ?? '');
        $this->scheduled_hour_meter = (string) ($row->scheduled_hour_meter ?? '');
        $this->priority = $row->priority;
        $this->status = $row->status;
        $this->cost = (string) ($row->cost ?? '');
        $this->observations = (string) ($row->observations ?? '');
        $this->responsible_user_id = $row->responsible_user_id;
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
            'maintenance_type' => ['required', 'string', 'max:120'],
            'scheduled_date' => ['required', 'date'],
            'scheduled_odometer' => ['nullable', 'numeric'],
            'scheduled_hour_meter' => ['nullable', 'numeric'],
            'priority' => ['required', Rule::in(DocumentPriority::values())],
            'status' => ['required', Rule::in(array_map(fn ($s) => $s->value(), FleetPreventiveMaintenanceStatus::cases()))],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'observations' => ['nullable', 'string'],
            'responsible_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $payload = [
            'company_id' => $company->id,
            ...$validated,
            'scheduled_odometer' => $validated['scheduled_odometer'] !== '' ? $validated['scheduled_odometer'] : null,
            'scheduled_hour_meter' => $validated['scheduled_hour_meter'] !== '' ? $validated['scheduled_hour_meter'] : null,
            'cost' => $validated['cost'] !== '' ? $validated['cost'] : null,
        ];

        if ($this->editingId) {
            FleetPreventiveMaintenance::query()->findOrFail($this->editingId)->update($payload);
        } else {
            DB::transaction(function () use ($payload, $company): void {
                $payload['code'] = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::FleetPreventiveMaintenance);
                FleetPreventiveMaintenance::query()->create($payload);
            });
        }

        $this->resetForm();
        $this->successToast('Programacion guardada.');
    }

    public function deleteRow(int $id): void
    {
        abort_unless(auth()->user()?->can('mecanica.eliminar'), 403);
        FleetPreventiveMaintenance::query()->findOrFail($id)->delete();
        $this->warningToast('Registro eliminado.');
    }

    public function close(): void
    {
        $this->showFormModal = false;
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'fleet_equipment_id', 'maintenance_type', 'scheduled_date', 'scheduled_odometer', 'scheduled_hour_meter', 'cost', 'observations', 'responsible_user_id']);
        $this->scheduled_date = DefaultDate::today();
        $this->resetFleetEquipmentSearch();
        $this->priority = DocumentPriority::Medium->value();
        $this->status = FleetPreventiveMaintenanceStatus::Scheduled->value();
        $this->showFormModal = false;
    }

    protected function responsibleUsers(): Collection
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        return $company?->users()->wherePivot('active', true)->orderBy('name')->get() ?? collect();
    }
}
