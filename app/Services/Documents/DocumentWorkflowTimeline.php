<?php

namespace App\Services\Documents;

use App\Models\Document;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

class DocumentWorkflowTimeline
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function build(Document $document): Collection
    {
        $movementItems = $document->movements->map(function ($movement): array {
            $attachments = $movement->relationLoaded('media')
                ? $movement->media
                : $movement->getMedia('attachments');

            if (! $attachments instanceof MediaCollection) {
                $attachments = new MediaCollection;
            }

            return [
                'type' => 'movement',
                'title' => str($movement->action)->replace('_', ' ')->headline()->toString(),
                'description' => $movement->notes ?: 'Movimiento registrado en la bitacora.',
                'actor' => $movement->user?->name,
                'created_at' => $movement->created_at,
                'status' => $movement->to_status,
                'meta' => [
                    'from_area' => $movement->fromArea?->name,
                    'to_area' => $movement->toArea?->name,
                    'from_user' => $movement->fromUser?->name,
                    'to_user' => $movement->toUser?->name,
                    'attachment_count' => $attachments->count(),
                ],
                'attachments' => $attachments->map(fn ($media): array => [
                    'name' => $media->file_name,
                    'url' => $media->getUrl(),
                ])->all(),
            ];
        });

        $observationItems = $document->movementObservations->map(function ($observation): array {
            return [
                'type' => 'observation',
                'title' => 'Observacion',
                'description' => $observation->observation,
                'actor' => $observation->user?->name,
                'created_at' => $observation->created_at,
                'status' => $observation->status_after,
                'meta' => [],
                'attachments' => [],
            ];
        });

        $approvalItems = $document->approvals->map(function ($approval): array {
            return [
                'type' => 'approval',
                'title' => $approval->decision === 'approved' ? 'Aprobacion' : 'Rechazo',
                'description' => $approval->comments ?: 'Decision registrada.',
                'actor' => $approval->user?->name,
                'created_at' => $approval->resolved_at ?? $approval->created_at,
                'status' => $approval->decision === 'approved' ? 'aprobado' : 'rechazado',
                'meta' => [],
                'attachments' => [],
            ];
        });

        return $movementItems
            ->concat($observationItems)
            ->concat($approvalItems)
            ->sortBy('created_at')
            ->values();
    }
}
