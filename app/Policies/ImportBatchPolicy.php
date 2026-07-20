<?php

namespace App\Policies;

use App\Models\ImportBatch;
use App\Models\User;

class ImportBatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Super Admin', 'Staff']);
    }

    public function view(User $user, ImportBatch $importBatch): bool
    {
        return $user->hasAnyRole(['Admin', 'Super Admin'])
            || ($user->hasRole('Staff') && (int) $importBatch->created_by === (int) $user->getKey());
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Super Admin', 'Staff']);
    }
}
