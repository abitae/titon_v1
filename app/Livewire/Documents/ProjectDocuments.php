<?php

namespace App\Livewire\Documents;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectDocuments extends Component
{
    use WithPagination;

    public string $title = 'Documentos por obra';

    public string $projectFilter = '';

    public string $statusFilter = '';

    public string $search = '';

    public function render(): View
    {
        $documents = Document::query()
            ->with(['project', 'documentType', 'currentUser'])
            ->when($this->projectFilter !== '', fn ($query) => $query->where('work_project_id', $this->projectFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery
                        ->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('subject', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->paginate(12);

        return view('livewire.documents.project-documents', [
            'documents' => $documents,
            'projects' => Project::query()->orderBy('name')->get(),
            'statusOptions' => DocumentStatus::cases(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }
}
