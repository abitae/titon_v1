<?php

namespace App\Actions\Documents;

use App\Enums\DocumentMovementType;
use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\User;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TransitionDocument
{
    public function __construct(
        protected RecordDocumentMovement $recordDocumentMovement,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $metadata
     * @param  array<int, TemporaryUploadedFile|UploadedFile>  $attachments
     */
    public function handle(
        Document $document,
        User $actor,
        DocumentMovementType $action,
        DocumentStatus $status,
        array $attributes = [],
        ?string $notes = null,
        array $metadata = [],
        array $attachments = [],
    ): Document {
        $fromStatus = $document->status;
        $fromAreaId = $document->origin_area_id;
        $fromUserId = $document->current_user_id;

        $document->fill([
            ...$attributes,
            'status' => $status->value(),
        ]);
        $document->save();

        $this->recordDocumentMovement->handle(
            document: $document,
            user: $actor,
            action: $action,
            fromStatus: $fromStatus,
            toStatus: $document->status,
            notes: $notes,
            fromAreaId: $fromAreaId,
            toAreaId: $document->destination_area_id,
            fromUserId: $fromUserId,
            toUserId: $document->current_user_id,
            metadata: $metadata,
            attachments: $attachments,
        );

        return $document->refresh();
    }
}
