<?php

namespace App\Actions\Requirements;

use App\Enums\RequirementStatus;
use App\Models\Requirement;

class SubmitRequirement
{
    public function handle(Requirement $requirement): Requirement
    {
        abort_unless($requirement->status === RequirementStatus::Draft->value(), 422, 'Solo borradores pueden publicarse.');

        $requirement->update(['status' => RequirementStatus::Created->value()]);

        return $requirement->refresh();
    }
}
