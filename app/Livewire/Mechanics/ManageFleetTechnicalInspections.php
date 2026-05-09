<?php

namespace App\Livewire\Mechanics;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithToast;
use App\Enums\CorrelativeSubject;
use App\Models\FleetEquipment;
use App\Models\FleetTechnicalInspection;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageFleetTechnicalInspections extends Component
{
    use InteractsWithToast, WithFileUploads, WithPagination;

    public string $title = 'Revisiones tecnicas';

    public bool $showFormModal = false;

    public ?int $editingId = null;

    public ?int $fleet_equipment_id = null;

    public string $reviewed_at = '';

    public string $due_at = '';

    public string $result = '';

    public string $inspection_center = '';

    public ?int $responsible_user_id = null;

    public string $observations = '';

    public $certificate_files = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('revisiones.ver'), 403);
    }

    public function render(): View
    {
        return view('livewire.mechanics.manage-fleet-technical-inspections', [
            'rows' => FleetTechnicalInspection::query()
                ->with(['equipment', 'responsibleUser'])
                ->latest('due_at')
                ->paginate(12),
            'equipments' => FleetEquipment::query()->orderBy('internal_code')->get(),
            'responsibleUsers' => $this->responsibleUsers(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('revisiones.crear'), 403);
        $this->resetForm();
        $this->fleet_equipment_id = FleetEquipment::query()->orderBy('internal_code')->value('id');
        if ($this->fleet_equipment_id === null) {
            $this->dangerToast('Registre primero un equipo.');

            return;
        }

        $this->showFormModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('revisiones.crear'), 403);
        $row = FleetTechnicalInspection::query()->findOrFail($id);
        $this->editingId = $row->id;
        $this->fleet_equipment_id = $row->fleet_equipment_id;
        $this->reviewed_at = $row->reviewed_at?->format('Y-m-d') ?? '';
        $this->due_at = $row->due_at?->format('Y-m-d') ?? '';
        $this->result = $row->result;
        $this->inspection_center = (string) ($row->inspection_center ?? '');
        $this->responsible_user_id = $row->responsible_user_id;
        $this->observations = (string) ($row->observations ?? '');
        $this->certificate_files = [];
        $this->showFormModal = true;
    }

    public function save(ResolveCurrentCompany $resolveCurrentCompany): void
    {
        abort_unless(auth()->user()?->can('revisiones.crear'), 403);
        $company = $resolveCurrentCompany->handle(auth()->user());
        abort_if($company === null, 403);

        $validated = $this->validate([
            'fleet_equipment_id' => ['required', Rule::exists('fleet_equipments', 'id')->where(fn ($q) => $q->where('company_id', $company->id))],
            'reviewed_at' => ['required', 'date'],
            'due_at' => ['required', 'date', 'after_or_equal:reviewed_at'],
            'result' => ['required', 'string', 'max:255'],
            'inspection_center' => ['nullable', 'string', 'max:255'],
            'responsible_user_id' => ['nullable', 'exists:users,id'],
            'observations' => ['nullable', 'string'],
            'certificate_files.*' => ['nullable', 'file', 'max:10240'],
        ]);

        $payload = [
            'company_id' => $company->id,
            'fleet_equipment_id' => $validated['fleet_equipment_id'],
            'reviewed_at' => $validated['reviewed_at'],
            'due_at' => $validated['due_at'],
            'result' => $validated['result'],
            'inspection_center' => $validated['inspection_center'] ?: null,
            'responsible_user_id' => $validated['responsible_user_id'],
            'observations' => $validated['observations'] ?: null,
            'status' => 'vigente',
        ];

        if ($this->editingId) {
            $model = FleetTechnicalInspection::query()->findOrFail($this->editingId);
            $model->update($payload);
        } else {
            $model = DB::transaction(function () use ($payload, $company): FleetTechnicalInspection {
                $payload['code'] = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::FleetTechnicalInspection);

                return FleetTechnicalInspection::query()->create($payload);
            });
        }

        foreach ($this->certificate_files as $uploadedFile) {
            $model->addMedia($uploadedFile->getRealPath())
                ->usingFileName($uploadedFile->getClientOriginalName())
                ->toMediaCollection('certificate', 'public');
        }

        $this->resetForm();
        $this->successToast('Revision guardada.');
    }

    public function deleteRow(int $id): void
    {
        abort_unless(auth()->user()?->can('mecanica.eliminar'), 403);
        FleetTechnicalInspection::query()->findOrFail($id)->delete();
        $this->warningToast('Revision eliminada.');
    }

    public function close(): void
    {
        $this->showFormModal = false;
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'fleet_equipment_id', 'reviewed_at', 'due_at', 'result', 'inspection_center', 'responsible_user_id', 'observations', 'certificate_files']);
        $this->showFormModal = false;
    }

    protected function responsibleUsers(): Collection
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        return $company?->users()->wherePivot('active', true)->orderBy('name')->get() ?? collect();
    }
}
