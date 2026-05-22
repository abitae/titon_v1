<?php

namespace App\Actions\Requirements;

use App\Enums\RequirementStatus;
use App\Models\Requirement;

class CancelRequirement
{
    public function handle(Requirement $requirement, string $reason): Requirement
    {
        abort_if(trim($reason) === '', 422, 'El motivo de cancelación es obligatorio.');

        $requirement->update([
            'status' => RequirementStatus::Cancelled->value(),
            'cancellation_reason' => $reason,
        ]);

        return $requirement->refresh();
    }
}
