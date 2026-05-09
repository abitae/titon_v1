<?php

namespace App\Policies;

use App\Models\Audit;
use App\Models\User;

class AuditPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('audits.ver');
    }

    public function view(User $user, Audit $audit): bool
    {
        return $user->can('audits.ver');
    }
}
