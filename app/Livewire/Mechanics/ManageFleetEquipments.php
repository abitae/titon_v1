<?php

namespace App\Livewire\Mechanics;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithToast;
use App\Concerns\ViewsPdfInModal;
use App\Enums\CatalogType;
use App\Enums\CorrelativeSubject;
use App\Enums\FleetEquipmentOperationalStatus;
use App\Enums\FleetWorkOrderType;
use App\Models\CatalogItem;
use App\Models\FleetEquipment;
use App\Models\Project;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use App\Support\DefaultDate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageFleetEquipments extends Component
{
    use InteractsWithToast, ViewsPdfInModal, WithFileUploads, WithPagination;

    public function openEquipmentsReportPdf(): void
    {
        $this->openRoutePdfModal('mechanics.report.equipments.pdf', 'Equipos y maquinarias');
    }

    public function openEquipmentHistoryPdf(int $equipmentId): void
    {
        $equipment = FleetEquipment::query()->findOrFail($equipmentId);

        $this->openRoutePdfModal(
            'mechanics.equipments.history.pdf',
            'Historial '.$equipment->internal_code,
            ['fleetEquipment' => $equipment],
            'Historial completo del equipo',
        );
    }

    public function openEquipmentDocument(string $url, string $name, string $mimeType): void
    {
        if ($mimeType === 'application/pdf' || str_ends_with(mb_strtolower($name), '.pdf')) {
            $this->openPdfModal($url, $name, 'Documento adjunto del equipo');

            return;
        }

        $this->redirect($url, navigate: false);
    }

    public string $title = 'Equipos y maquinarias';

    public string $search = '';

    public string $statusFilter = '';

    public string $projectFilter = '';

    public ?int $editingEquipmentId = null;

    public bool $showFormModal = false;

    public bool $showDetailModal = false;

    public ?FleetEquipment $selectedEquipment = null;

    public array $equipment_photos = [];

    public array $equipment_documents = [];

    public string $internal_code = '';

    public ?int $equipment_type_id = null;

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
            ->with(['workProject', 'responsibleUser', 'equipmentType', 'media'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nested): void {
                    $nested->where('internal_code', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%')
                        ->orWhere('plate', 'like', '%'.$this->search.'%')
                        ->orWhereHas('workProject', function ($projectQuery): void {
                            $projectQuery
                                ->where('code', 'like', '%'.$this->search.'%')
                                ->orWhere('name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('operational_status', $this->statusFilter))
            ->when($this->projectFilter !== '', fn ($query) => $query->where('work_project_id', (int) $this->projectFilter))
            ->latest()
            ->paginate(12);

        return view('livewire.mechanics.manage-fleet-equipments', [
            'equipments' => $equipment,
            'projects' => Project::query()->select(['id', 'code', 'name'])->orderBy('code')->get(),
            'responsibleUsers' => $this->responsibleUsers(),
            'statusOptions' => FleetEquipmentOperationalStatus::cases(),
            'equipmentTypes' => CatalogItem::query()
                ->ofType(CatalogType::EquipmentType)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
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
        abort_unless(auth()->user()?->can('equipos.crear'), 403);

        if (! CatalogItem::query()
            ->ofType(CatalogType::EquipmentType)
            ->where('is_active', true)
            ->exists()) {
            $this->dangerToast('Registre primero un tipo de equipo en Tipos de equipo.', 'Sin tipos disponibles');

            return;
        }

        $this->resetForm();
        $this->equipment_type_id = CatalogItem::query()
            ->ofType(CatalogType::EquipmentType)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->value('id');
        $this->showFormModal = true;
    }

    public function openEditModal(int $equipmentId): void
    {
        abort_unless(auth()->user()?->can('equipos.editar'), 403);
        $equipment = FleetEquipment::query()->findOrFail($equipmentId);
        $this->editingEquipmentId = $equipment->id;
        $this->internal_code = $equipment->internal_code;
        $this->equipment_type_id = $equipment->equipment_type_id;
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
            ->with([
                'workProject',
                'responsibleUser',
                'equipmentType',
                'media',
                'technicalInspections' => fn ($query) => $query
                    ->with('responsibleUser')
                    ->orderByDesc('reviewed_at')
                    ->orderByDesc('due_at'),
                'preventiveMaintenances' => fn ($query) => $query
                    ->with('responsibleUser')
                    ->orderByDesc('scheduled_date'),
                'correctiveMaintenances' => fn ($query) => $query
                    ->with('responsibleUser')
                    ->orderByDesc('failure_at'),
                'workOrders' => fn ($query) => $query
                    ->with(['responsibleUser', 'workProject'])
                    ->orderByDesc('issued_at'),
            ])
            ->findOrFail($equipmentId);

        $this->showDetailModal = true;
    }

    public function saveEquipment(ResolveCurrentCompany $resolveCurrentCompany): void
    {
        $company = $resolveCurrentCompany->handle(auth()->user());
        abort_if($company === null, 403);
        abort_unless(auth()->user()->can($this->editingEquipmentId ? 'equipos.editar' : 'equipos.crear'), 403);

        $activeCompanyUserIds = $company->users()
            ->wherePivot('active', true)
            ->pluck('users.id')
            ->all();

        $validated = $this->validateWithToastFeedback(
            $this->rules($company->id, $activeCompanyUserIds),
            $this->validationMessages(),
            $this->validationAttributes(),
        );
        $wasEditing = $this->editingEquipmentId !== null;

        $equipmentType = CatalogItem::query()
            ->ofType(CatalogType::EquipmentType)
            ->where('company_id', $company->id)
            ->findOrFail($validated['equipment_type_id']);

        if ($wasEditing) {
            $internalCode = FleetEquipment::query()->findOrFail($this->editingEquipmentId)->internal_code;
        } else {
            $internalCode = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::FleetEquipment);
        }

        $payload = [
            'company_id' => $company->id,
            'internal_code' => $internalCode,
            'equipment_type_id' => $equipmentType->id,
            'equipment_type' => $equipmentType->name,
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
        $this->successToast(
            $wasEditing ? 'Equipo actualizado correctamente.' : 'Equipo registrado correctamente.',
            $wasEditing ? 'Cambios guardados' : 'Registro exitoso',
        );
    }

    public function deleteEquipment(int $equipmentId): void
    {
        abort_unless(auth()->user()?->can('equipos.eliminar'), 403);

        $equipment = FleetEquipment::query()->findOrFail($equipmentId);
        $label = $equipment->internal_code;
        $equipment->delete();
        $this->resetPage();
        $this->warningToast("Se elimino el equipo {$label}.", 'Equipo eliminado');
    }

    public function closeModals(): void
    {
        $this->showFormModal = false;
        $this->showDetailModal = false;
        $this->selectedEquipment = null;
    }

    /**
     * @param  list<int>  $activeCompanyUserIds
     * @return array<string, mixed>
     */
    protected function rules(int $companyId, array $activeCompanyUserIds): array
    {
        $currentYear = (int) now()->format('Y');

        return [
            'equipment_type_id' => [
                'required',
                'integer',
                Rule::exists('catalog_items', 'id')->where(fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->where('type', CatalogType::EquipmentType->value())
                    ->where('is_active', true)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'plate' => ['nullable', 'string', 'max:24'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.($currentYear + 1)],
            'color' => ['nullable', 'string', 'max:64'],
            'city' => ['nullable', 'string', 'max:120'],
            'work_project_id' => [
                'nullable',
                'integer',
                Rule::exists('projects', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'responsible_user_id' => ['nullable', 'integer', Rule::in($activeCompanyUserIds)],
            'operational_status' => ['required', Rule::in(FleetEquipmentOperationalStatus::values())],
            'odometer_km' => ['nullable', 'numeric', 'min:0'],
            'hour_meter' => ['nullable', 'numeric', 'min:0'],
            'acquisition_date' => ['nullable', 'date'],
            'observations' => ['nullable', 'string', 'max:2000'],
            'equipment_photos.*' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif'],
            'equipment_documents.*' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationMessages(): array
    {
        return [
            'equipment_type_id.required' => 'Seleccione un tipo de equipo.',
            'equipment_type_id.exists' => 'El tipo de equipo seleccionado no es valido o esta inactivo.',
            'name.required' => 'Ingrese el nombre del equipo.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'year.integer' => 'El año debe ser un numero entero.',
            'year.min' => 'El año debe ser 1900 o posterior.',
            'year.max' => 'El año no puede ser mayor al proximo año calendario.',
            'work_project_id.exists' => 'La obra seleccionada no pertenece a la empresa activa.',
            'responsible_user_id.in' => 'El responsable debe ser un usuario activo de la empresa.',
            'operational_status.required' => 'Seleccione el estado operativo.',
            'operational_status.in' => 'El estado operativo seleccionado no es valido.',
            'odometer_km.numeric' => 'El kilometraje debe ser un numero.',
            'odometer_km.min' => 'El kilometraje no puede ser negativo.',
            'hour_meter.numeric' => 'El horometro debe ser un numero.',
            'hour_meter.min' => 'El horometro no puede ser negativo.',
            'acquisition_date.date' => 'La fecha de adquisicion no es valida.',
            'equipment_photos.*.max' => 'Cada foto no puede superar los 10 MB.',
            'equipment_photos.*.mimes' => 'Las fotos deben ser JPG, PNG, WEBP o GIF.',
            'equipment_documents.*.max' => 'Cada documento no puede superar los 10 MB.',
            'equipment_documents.*.mimes' => 'Los documentos deben ser PDF o imagen (JPG/PNG).',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'equipment_type_id' => 'tipo de equipo',
            'name' => 'nombre',
            'brand' => 'marca',
            'model' => 'modelo',
            'serial_number' => 'serie',
            'plate' => 'placa',
            'year' => 'año',
            'color' => 'color',
            'city' => 'ciudad',
            'work_project_id' => 'obra',
            'responsible_user_id' => 'responsable',
            'operational_status' => 'estado operativo',
            'odometer_km' => 'kilometraje',
            'hour_meter' => 'horometro',
            'acquisition_date' => 'fecha de adquisicion',
            'observations' => 'observaciones',
            'equipment_photos.*' => 'foto',
            'equipment_documents.*' => 'documento',
        ];
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingEquipmentId',
            'equipment_photos',
            'equipment_documents',
            'internal_code',
            'equipment_type_id',
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
        $this->acquisition_date = DefaultDate::today();
        $this->operational_status = FleetEquipmentOperationalStatus::Operational->value();
        $this->showFormModal = false;
    }

    protected function storeMedia(FleetEquipment $equipment): void
    {
        try {
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
        } catch (\Throwable $exception) {
            $this->dangerToast('No se pudieron guardar algunos archivos adjuntos.', 'Error al subir archivos');
            report($exception);

            return;
        }

        $this->equipment_photos = [];
        $this->equipment_documents = [];
    }

    protected function responsibleUsers(): Collection
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        return $company?->users()->wherePivot('active', true)->orderBy('name')->get() ?? collect();
    }

    public function preventiveCreateUrl(int $equipmentId): string
    {
        return route('mechanics.preventive', ['create' => 1, 'equipment' => $equipmentId]);
    }

    public function correctiveCreateUrl(int $equipmentId): string
    {
        return route('mechanics.corrective', ['create' => 1, 'equipment' => $equipmentId]);
    }

    public function workOrderCreateUrl(int $equipmentId, string $type = 'preventivo'): string
    {
        $type = in_array($type, FleetWorkOrderType::values(), true) ? $type : FleetWorkOrderType::Preventive->value();

        return route('mechanics.work-orders', ['create' => 1, 'equipment' => $equipmentId, 'type' => $type]);
    }
}
