<?php

namespace App\Livewire\Projects;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithToast;
use App\Enums\CatalogType;
use App\Enums\CorrelativeSubject;
use App\Enums\ProjectStatus;
use App\Models\CatalogItem;
use App\Models\Project;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageProjects extends Component
{
    use InteractsWithToast, WithFileUploads, WithPagination;

    public string $title = 'Obras';

    public string $search = '';

    public string $cityFilter = '';

    public string $statusFilter = '';

    public string $responsibleFilter = '';

    public ?int $editingProjectId = null;

    public bool $showFormModal = false;

    public bool $showDetailModal = false;

    public ?Project $selectedProject = null;

    public array $attachments = [];

    public string $code = '';

    public string $name = '';

    public string $city = '';

    public string $address = '';

    public string $client_name = '';

    public string $start_date = '';

    public string $estimated_end_date = '';

    public string $estimated_budget = '';

    public string $status = 'planificada';

    public string $description = '';

    public ?int $responsible_user_id = null;

    public function render(): View
    {
        $projects = Project::query()
            ->with(['responsibleUser', 'attachments'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery
                        ->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%')
                        ->orWhere('client_name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->cityFilter !== '', fn ($query) => $query->where('city', $this->cityFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->responsibleFilter !== '', fn ($query) => $query->where('responsible_user_id', $this->responsibleFilter))
            ->latest()
            ->paginate(10);

        $summary = [
            'total' => Project::query()->count(),
            'in_progress' => Project::query()->where('status', ProjectStatus::InProgress->value())->count(),
            'estimated_budget' => Project::query()->sum('estimated_budget'),
        ];

        return view('livewire.projects.manage-projects', [
            'projects' => $projects,
            'summary' => $summary,
            'cities' => CatalogItem::query()->ofType(CatalogType::City)->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'responsibleUsers' => $this->responsibleUsers(),
            'statusOptions' => ProjectStatus::cases(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedResponsibleFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $projectId): void
    {
        $project = Project::query()->with('attachments')->findOrFail($projectId);

        $this->editingProjectId = $project->id;
        $this->code = $project->code;
        $this->name = $project->name;
        $this->city = $project->city ?? '';
        $this->address = $project->address ?? '';
        $this->client_name = $project->client_name ?? '';
        $this->responsible_user_id = $project->responsible_user_id;
        $this->start_date = $project->start_date?->format('Y-m-d') ?? '';
        $this->estimated_end_date = $project->estimated_end_date?->format('Y-m-d') ?? '';
        $this->estimated_budget = (string) $project->estimated_budget;
        $this->status = $project->status;
        $this->description = $project->description ?? '';
        $this->attachments = [];
        $this->showFormModal = true;
    }

    public function openDetailModal(int $projectId): void
    {
        $this->selectedProject = Project::query()
            ->with(['responsibleUser', 'attachments'])
            ->findOrFail($projectId);

        $this->showDetailModal = true;
    }

    public function saveProject(): void
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        abort_if($company === null, 403);
        abort_unless(auth()->user()->can($this->editingProjectId ? 'projects.editar' : 'projects.crear'), 403);

        $validated = $this->validate($this->rules($company->id));
        $isEditing = $this->editingProjectId !== null;

        $project = DB::transaction(function () use ($validated, $company, $isEditing): Project {
            $finalCode = trim((string) ($validated['code'] ?? ''));

            if (! $isEditing && $finalCode === '') {
                $finalCode = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::Project);
            }

            return Project::query()->updateOrCreate(
                ['id' => $this->editingProjectId],
                [
                    ...$validated,
                    'code' => $isEditing ? $validated['code'] : $finalCode,
                    'company_id' => $company->id,
                    'estimated_budget' => $validated['estimated_budget'] === '' ? 0 : $validated['estimated_budget'],
                ],
            );
        });

        $this->storeAttachments($project);
        $this->resetForm();
        $this->successToast($isEditing ? 'Obra actualizada correctamente.' : 'Obra creada correctamente.');
        $this->dispatch('project-saved');
    }

    public function deleteProject(int $projectId): void
    {
        abort_unless(auth()->user()->can('projects.eliminar'), 403);

        Project::query()->findOrFail($projectId)->delete();
        $this->resetPage();
        $this->warningToast('Obra eliminada correctamente.');
    }

    public function closeModals(): void
    {
        $this->showFormModal = false;
        $this->showDetailModal = false;
        $this->selectedProject = null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(int $companyId): array
    {
        return [
            'code' => [
                Rule::requiredIf($this->editingProjectId !== null),
                'nullable',
                'string',
                'max:50',
                Rule::unique('projects', 'code')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($this->editingProjectId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'estimated_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'estimated_budget' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(ProjectStatus::values())],
            'description' => ['nullable', 'string'],
            'attachments.*' => ['nullable', 'file', 'max:10240'],
        ];
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingProjectId',
            'attachments',
            'code',
            'name',
            'city',
            'address',
            'client_name',
            'responsible_user_id',
            'start_date',
            'estimated_end_date',
            'estimated_budget',
            'description',
        ]);

        $this->status = ProjectStatus::Planned->value();
        $this->showFormModal = false;
    }

    protected function storeAttachments(Project $project): void
    {
        foreach ($this->attachments as $uploadedFile) {
            $path = $uploadedFile->store('attachments/projects', 'public');

            $project->attachments()->create([
                'company_id' => $project->company_id,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
            ]);
        }
    }

    protected function responsibleUsers()
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        return $company?->users()->wherePivot('active', true)->orderBy('name')->get() ?? collect();
    }
}
