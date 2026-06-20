<?php

namespace App\Policies;

use App\Models\ImportBatch;
use App\Models\User;

class ImportBatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Staff');
    }

    public function view(User $user, ImportBatch $importBatch): bool
    {
        return $user->hasRole('Staff');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Staff');
    }
}
