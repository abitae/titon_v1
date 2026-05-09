<?php

namespace App\Livewire\Documents;

use App\Enums\DocumentPriority;
use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ManageOutbox extends Component
{
    use WithPagination;

    public string $title = 'Bandeja de salida';

    public string $search = '';

    public string $statusFilter = '';

    public string $priorityFilter = '';

    public string $projectFilter = '';

    public function render(): View
    {
        $documents = Document::query()
            ->with(['project', 'documentType', 'currentUser', 'destinationArea'])
            ->where('created_by_user_id', auth()->id())
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery
                        ->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('subject', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->priorityFilter !== '', fn ($query) => $query->where('priority', $this->priorityFilter))
            ->when($this->projectFilter !== '', fn ($query) => $query->where('work_project_id', $this->projectFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.documents.manage-outbox', [
            'documents' => $documents,
            'projects' => Project::query()->orderBy('name')->get(),
            'statusOptions' => DocumentStatus::cases(),
            'priorityOptions' => DocumentPriority::cases(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }
}
