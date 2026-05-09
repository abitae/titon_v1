<?php

namespace App\Livewire\Mechanics;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithToast;
use App\Enums\CorrelativeSubject;
use App\Enums\FleetEquipmentOperationalStatus;
use App\Models\FleetEquipment;
use App\Models\Project;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageFleetEquipments extends Component
{
    use InteractsWithToast, WithFileUploads, WithPagination;

    public string $title = 'Equipos y maquinarias';

    public string $search = '';

    public string $statusFilter = '';

    public ?int $editingEquipmentId = null;

    public bool $showFormModal = false;

    public bool $showDetailModal = false;

    public ?FleetEquipment $selectedEquipment = null;

    public array $equipment_photos = [];

    public array $equipment_documents = [];

    public string $internal_code = '';

    public string $equipment_type = '';

    public string $name = '';

    public string $brand = '';

    public string $model = '';

    public string $serial_number = '';

    public string $plate = '';

    public string $year = '';

    public string $color = '';

    public string $city = '';

    public ?int $work_project_id = null;

    public ?int $responsible_user_id = null;

    public string $operational_status = '';

    public string $odometer_km = '';

    public string $hour_meter = '';

    public string $acquisition_date = '';

    public string $observations = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('equipos.ver'), 403);
        $this->operational_status = FleetEquipmentOperationalStatus::Operational->value();
    }

    public function render(): View
    {
        $equipment = FleetEquipment::query()
            ->with(['workProject', 'responsibleUser', 'media'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nested): void {
                    $nested->where('internal_code', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%')
                        ->orWhere('plate', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('operational_status', $this->statusFilter))
            ->latest()
            ->paginate(12);

        return view('livewire.mechanics.manage-fleet-equipments', [
            'equipments' => $equipment,
            'projects' => Project::query()->select(['id', 'code', 'name'])->orderBy('code')->get(),
            'responsibleUsers' => $this->responsibleUsers(),
            'statusOptions' => FleetEquipmentOperationalStatus::cases(),
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

    public function openCreateModal(): void
    {
        abort_unless(auth()->user()?->can('equipos.crear'), 403);
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $equipmentId): void
    {
        abort_unless(auth()->user()?->can('equipos.editar'), 403);
        $equipment = FleetEquipment::query()->findOrFail($equipmentId);
        $this->editingEquipmentId = $equipment->id;
        $this->internal_code = $equipment->internal_code;
        $this->equipment_type = $equipment->equipment_type;
        $this->name = $equipment->name;
        $this->brand = (string) ($equipment->brand ?? '');
        $this->model = (string) ($equipment->model ?? '');
        $this->serial_number = (string) ($equipment->serial_number ?? '');
        $this->plate = (string) ($equipment->plate ?? '');
        $this->year = $equipment->year !== null ? (string) $equipment->year : '';
        $this->color = (string) ($equipment->color ?? '');
        $this->city = (string) ($equipment->city ?? '');
        $this->work_project_id = $equipment->work_project_id;
        $this->responsible_user_id = $equipment->responsible_user_id;
        $this->operational_status = $equipment->operational_status;
        $this->odometer_km = (string) ($equipment->odometer_km ?? '');
        $this->hour_meter = (string) ($equipment->hour_meter ?? '');
        $this->acquisition_date = $equipment->acquisition_date?->format('Y-m-d') ?? '';
        $this->observations = (string) ($equipment->observations ?? '');
        $this->equipment_photos = [];
        $this->equipment_documents = [];
        $this->showFormModal = true;
    }

    public function openDetailModal(int $equipmentId): void
    {
        $this->selectedEquipment = FleetEquipment::query()
            ->with(['workProject', 'responsibleUser', 'media'])
            ->findOrFail($equipmentId);

        $this->showDetailModal = true;
    }

    public function saveEquipment(ResolveCurrentCompany $resolveCurrentCompany): void
    {
        $company = $resolveCurrentCompany->handle(auth()->user());
        abort_if($company === null, 403);
        abort_unless(auth()->user()->can($this->editingEquipmentId ? 'equipos.editar' : 'equipos.crear'), 403);

        $validated = $this->validate($this->rules($company->id));
        $wasEditing = $this->editingEquipmentId !== null;

        if ($wasEditing) {
            $internalCode = trim((string) $validated['internal_code']);
        } else {
            $incoming = trim((string) ($validated['internal_code'] ?? ''));
            $internalCode = $incoming !== ''
                ? $incoming
                : app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::FleetEquipment);
        }

        $payload = [
            'company_id' => $company->id,
            'internal_code' => $internalCode,
            'equipment_type' => $validated['equipment_type'],
            'name' => $validated['name'],
            'brand' => $validated['brand'] !== '' ? $validated['brand'] : null,
            'model' => $validated['model'] !== '' ? $validated['model'] : null,
            'serial_number' => $validated['serial_number'] !== '' ? $validated['serial_number'] : null,
            'plate' => $validated['plate'] !== '' ? $validated['plate'] : null,
            'year' => $validated['year'] !== '' && $validated['year'] !== null ? (int) $validated['year'] : null,
            'color' => $validated['color'] !== '' ? $validated['color'] : null,
            'city' => $validated['city'] !== '' ? $validated['city'] : null,
            'work_project_id' => $validated['work_project_id'],
            'responsible_user_id' => $validated['responsible_user_id'],
            'operational_status' => $validated['operational_status'],
            'odometer_km' => $validated['odometer_km'] !== '' ? $validated['odometer_km'] : null,
            'hour_meter' => $validated['hour_meter'] !== '' ? $validated['hour_meter'] : null,
            'acquisition_date' => $validated['acquisition_date'] !== '' ? $validated['acquisition_date'] : null,
            'observations' => $validated['observations'] !== '' ? $validated['observations'] : null,
        ];

        $equipment = DB::transaction(function () use ($payload): FleetEquipment {
            if ($this->editingEquipmentId !== null) {
                $equipment = FleetEquipment::query()->findOrFail($this->editingEquipmentId);
                $equipment->update($payload);

                return $equipment;
            }

            return FleetEquipment::query()->create($payload);
        });

        $this->storeMedia($equipment);
        $this->resetForm();
        $this->successToast($wasEditing ? 'Equipo actualizado.' : 'Equipo registrado.');
    }

    public function deleteEquipment(int $equipmentId): void
    {
        abort_unless(auth()->user()?->can('equipos.eliminar'), 403);
        FleetEquipment::query()->findOrFail($equipmentId)->delete();
        $this->resetPage();
        $this->warningToast('Equipo eliminado.');
    }

    public function closeModals(): void
    {
        $this->showFormModal = false;
        $this->showDetailModal = false;
        $this->selectedEquipment = null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(int $companyId): array
    {
        return [
            'internal_code' => [
                Rule::requiredIf($this->editingEquipmentId !== null),
                'nullable',
                'string',
                'max:50',
                Rule::unique('fleet_equipments', 'internal_code')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($this->editingEquipmentId),
            ],
            'equipment_type' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'plate' => ['nullable', 'string', 'max:24'],
            'year' => ['nullable', 'numeric'],
            'color' => ['nullable', 'string', 'max:64'],
            'city' => ['nullable', 'string', 'max:120'],
            'work_project_id' => [
                'nullable',
                Rule::exists('projects', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'responsible_user_id' => ['nullable', 'exists:users,id'],
            'operational_status' => ['required', Rule::in(FleetEquipmentOperationalStatus::values())],
            'odometer_km' => ['nullable', 'numeric', 'min:0'],
            'hour_meter' => ['nullable', 'numeric', 'min:0'],
            'acquisition_date' => ['nullable', 'date'],
            'observations' => ['nullable', 'string'],
            'equipment_photos.*' => ['nullable', 'file', 'max:10240'],
            'equipment_documents.*' => ['nullable', 'file', 'max:10240'],
        ];
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingEquipmentId',
            'equipment_photos',
            'equipment_documents',
            'internal_code',
            'equipment_type',
            'name',
            'brand',
            'model',
            'serial_number',
            'plate',
            'year',
            'color',
            'city',
            'work_project_id',
            'responsible_user_id',
            'odometer_km',
            'hour_meter',
            'acquisition_date',
            'observations',
        ]);
        $this->operational_status = FleetEquipmentOperationalStatus::Operational->value();
        $this->showFormModal = false;
    }

    protected function storeMedia(FleetEquipment $equipment): void
    {
        foreach ($this->equipment_photos as $uploadedFile) {
            $equipment->addMedia($uploadedFile->getRealPath())
                ->usingFileName($uploadedFile->getClientOriginalName())
                ->usingName(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME))
                ->toMediaCollection('equipment_photos', 'public');
        }

        foreach ($this->equipment_documents as $uploadedFile) {
            $equipment->addMedia($uploadedFile->getRealPath())
                ->usingFileName($uploadedFile->getClientOriginalName())
                ->usingName(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME))
                ->toMediaCollection('equipment_documents', 'public');
        }

        $this->equipment_photos = [];
        $this->equipment_documents = [];
    }

    protected function responsibleUsers(): Collection
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        return $company?->users()->wherePivot('active', true)->orderBy('name')->get() ?? collect();
    }
}
