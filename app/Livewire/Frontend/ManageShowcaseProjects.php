<?php

namespace App\Livewire\Frontend;

use App\Concerns\InteractsWithToast;
use App\Models\ShowcaseProject;
use App\Services\Frontend\SiteContentService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageShowcaseProjects extends Component
{
    use InteractsWithToast, WithFileUploads, WithPagination;

    public string $title = 'Portafolio web';

    public string $search = '';

    public string $publishedFilter = 'all';

    public bool $showFormModal = false;

    public ?int $editingProjectId = null;

    public string $projectTitle = '';

    public string $slug = '';

    public string $summary = '';

    public string $description = '';

    public string $city = '';

    public string $client_name = '';

    public bool $is_published = false;

    public bool $is_featured = false;

    public int $sort_order = 0;

    public ?TemporaryUploadedFile $image = null;

    public ?string $currentImageUrl = null;

    public function mount(): void
    {
        $this->authorizeSuperAdmin();
    }

    public function render(): View
    {
        $projects = ShowcaseProject::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery
                        ->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('city', 'like', '%'.$this->search.'%')
                        ->orWhere('client_name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->publishedFilter === 'published', fn ($query) => $query->where('is_published', true))
            ->when($this->publishedFilter === 'draft', fn ($query) => $query->where('is_published', false))
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('livewire.frontend.manage-showcase-projects', [
            'projects' => $projects,
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPublishedFilter(): void
    {
        $this->resetPage();
    }

    public function updatedProjectTitle(): void
    {
        if ($this->editingProjectId === null) {
            $this->slug = Str::slug($this->projectTitle);
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $projectId): void
    {
        $project = ShowcaseProject::query()->findOrFail($projectId);

        $this->editingProjectId = $project->id;
        $this->projectTitle = $project->title;
        $this->slug = $project->slug;
        $this->summary = $project->summary ?? '';
        $this->description = $project->description ?? '';
        $this->city = $project->city ?? '';
        $this->client_name = $project->client_name ?? '';
        $this->is_published = $project->is_published;
        $this->is_featured = $project->is_featured;
        $this->sort_order = $project->sort_order;
        $this->currentImageUrl = $project->imageUrl();
        $this->image = null;
        $this->showFormModal = true;
    }

    public function saveProject(SiteContentService $content): void
    {
        $this->authorizeSuperAdmin();

        $validated = $this->validate([
            'projectTitle' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('showcase_projects', 'slug')->ignore($this->editingProjectId),
            ],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:120'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'is_published' => ['required', 'bool'],
            'is_featured' => ['required', 'bool'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $isEditing = $this->editingProjectId !== null;
        $project = $isEditing
            ? ShowcaseProject::query()->findOrFail($this->editingProjectId)
            : new ShowcaseProject;

        $imagePath = $project->image_path;

        if ($this->image instanceof TemporaryUploadedFile) {
            if (filled($imagePath) && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $this->image->store('showcase-projects', 'public');
        }

        $project->fill([
            'title' => $validated['projectTitle'],
            'slug' => $validated['slug'],
            'summary' => $validated['summary'] ?: null,
            'description' => $validated['description'] ?: null,
            'city' => $validated['city'] ?: null,
            'client_name' => $validated['client_name'] ?: null,
            'is_published' => $validated['is_published'],
            'is_featured' => $validated['is_featured'],
            'sort_order' => $validated['sort_order'],
            'image_path' => $imagePath,
            'published_at' => $validated['is_published']
                ? ($project->published_at ?? now())
                : null,
        ]);
        $project->save();

        $content->forgetAll();

        $this->resetForm();
        $this->successToast($isEditing ? 'Proyecto actualizado correctamente.' : 'Proyecto creado correctamente.');
    }

    public function deleteProject(int $projectId, SiteContentService $content): void
    {
        $this->authorizeSuperAdmin();

        $project = ShowcaseProject::query()->findOrFail($projectId);

        if (filled($project->image_path) && Storage::disk('public')->exists($project->image_path)) {
            Storage::disk('public')->delete($project->image_path);
        }

        $project->delete();
        $content->forgetAll();
        $this->resetPage();
        $this->warningToast('Proyecto eliminado correctamente.');
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingProjectId',
            'projectTitle',
            'slug',
            'summary',
            'description',
            'city',
            'client_name',
            'image',
            'currentImageUrl',
        ]);

        $this->is_published = false;
        $this->is_featured = false;
        $this->sort_order = 0;
        $this->showFormModal = false;
    }

    protected function authorizeSuperAdmin(): void
    {
        abort_unless(Auth::user()?->hasRole('Super Admin'), 403);
    }
}
