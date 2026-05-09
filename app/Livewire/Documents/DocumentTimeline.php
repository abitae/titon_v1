<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Services\Documents\DocumentWorkflowTimeline;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class DocumentTimeline extends Component
{
    public string $title = 'Timeline del documento';

    public Document $document;

    public function mount(Document $document): void
    {
        $this->document = $document->load([
            'movements.user',
            'movements.fromArea',
            'movements.toArea',
            'movements.fromUser',
            'movements.toUser',
            'movements.media',
            'movementObservations.user',
            'approvals.user',
        ]);
    }

    public function render(DocumentWorkflowTimeline $documentWorkflowTimeline): View
    {
        return view('livewire.documents.document-timeline', [
            'timeline' => $documentWorkflowTimeline->build($this->document),
        ])->layout('layouts.app', ['title' => $this->title]);
    }
}
