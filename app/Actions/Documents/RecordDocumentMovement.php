<?php

namespace App\Actions\Documents;

use App\Enums\DocumentMovementType;
use App\Models\Document;
use App\Models\DocumentMovement;
use App\Models\User;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RecordDocumentMovement
{
    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<int, TemporaryUploadedFile|UploadedFile|array{name: string, contents: string}>  $attachments
     */
    public function handle(
        Document $document,
        User $user,
        DocumentMovementType $action,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?string $notes = null,
        ?int $fromAreaId = null,
        ?int $toAreaId = null,
        ?int $fromUserId = null,
        ?int $toUserId = null,
        array $metadata = [],
        array $attachments = [],
    ): DocumentMovement {
        $movement = $document->movements()->create([
            'company_id' => $document->company_id,
            'user_id' => $user->id,
            'from_area_id' => $fromAreaId,
            'to_area_id' => $toAreaId,
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'action' => $action->value(),
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
            'metadata' => $metadata,
        ]);

        foreach ($attachments as $attachment) {
            if (is_array($attachment)) {
                $movement
                    ->addMediaFromString($attachment['contents'])
                    ->usingFileName($attachment['name'])
                    ->usingName(pathinfo($attachment['name'], PATHINFO_FILENAME))
                    ->toMediaCollection('attachments', 'public');

                continue;
            }

            $contents = file_get_contents($attachment->getRealPath());

            if ($contents === false) {
                continue;
            }

            $movement
                ->addMediaFromString($contents)
                ->usingFileName($attachment->getClientOriginalName())
                ->usingName(pathinfo($attachment->getClientOriginalName(), PATHINFO_FILENAME))
                ->toMediaCollection('attachments', 'public');
        }

        return $movement;
    }
}
