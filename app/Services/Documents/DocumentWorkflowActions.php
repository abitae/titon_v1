<?php

namespace App\Services\Documents;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\User;

class DocumentWorkflowActions
{
    /**
     * @return list<string>
     */
    public function available(Document $document, User $user): array
    {
        $status = (string) $document->status;

        if ($status === DocumentStatus::Cancelled->value()) {
            return [];
        }

        if ($status === DocumentStatus::Closed->value()) {
            return $user->can('documents.editar') ? ['reopen'] : [];
        }

        $canEdit = $user->can('documents.editar');
        $canApprove = $user->can('documents.aprobar');
        $isAssignee = (int) $document->current_user_id === (int) $user->id;

        /** @var list<string> $actions */
        $actions = [];

        if ($canEdit && $isAssignee && in_array($status, [
            DocumentStatus::Registered->value(),
            DocumentStatus::Derived->value(),
            DocumentStatus::Expired->value(),
        ], true)) {
            $actions[] = 'receive';
        }

        if ($canEdit && in_array($status, [
            DocumentStatus::Received->value(),
            DocumentStatus::Observed->value(),
        ], true)) {
            $actions[] = 'process';
        }

        if ($canEdit && in_array($status, [
            DocumentStatus::Received->value(),
            DocumentStatus::InProgress->value(),
            DocumentStatus::InReview->value(),
            DocumentStatus::Observed->value(),
        ], true)) {
            $actions[] = 'attend';
        }

        if ($canEdit && $this->isOpenStatus($status)) {
            $actions[] = 'derive';
            $actions[] = 'observe';
        }

        if ($canApprove && in_array($status, [
            DocumentStatus::Received->value(),
            DocumentStatus::InProgress->value(),
            DocumentStatus::InReview->value(),
            DocumentStatus::Observed->value(),
            DocumentStatus::Attended->value(),
        ], true)) {
            $actions[] = 'approve';
            $actions[] = 'reject';
        }

        if ($canEdit && $status === DocumentStatus::Attended->value()) {
            $actions[] = 'archive';
        }

        if ($canEdit && in_array($status, [
            DocumentStatus::Approved->value(),
            DocumentStatus::Attended->value(),
        ], true)) {
            $actions[] = 'close';
        }

        if ($canEdit && $status === DocumentStatus::Archived->value()) {
            $actions[] = 'reopen';
        }

        if ($canEdit && $this->isOpenStatus($status)) {
            $actions[] = 'cancel';
        }

        return array_values(array_unique($actions));
    }

    protected function isOpenStatus(string $status): bool
    {
        return in_array($status, [
            DocumentStatus::Registered->value(),
            DocumentStatus::InProgress->value(),
            DocumentStatus::Derived->value(),
            DocumentStatus::Received->value(),
            DocumentStatus::Observed->value(),
            DocumentStatus::InReview->value(),
            DocumentStatus::Expired->value(),
        ], true);
    }
}
