<?php

namespace App\Policies;

use App\Models\Package;
use App\Models\User;

class PackagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Super Admin', 'Kabid', 'Staff']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Package $package): bool
    {
        return $user->hasAnyRole(['Admin', 'Super Admin', 'Kabid', 'Staff']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Super Admin', 'Staff']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Package $package): bool
    {
        return $user->hasAnyRole(['Admin', 'Super Admin'])
            || ($user->hasRole('Staff') && in_array($package->status, ['draft', 'needs_review'], true));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Package $package): bool
    {
        if ($user->hasAnyRole(['Admin', 'Super Admin'])) {
            return true;
        }

        if ($user->hasRole('Staff')) {
            return in_array($package->status, ['draft', 'needs_review']);
        }

        return false;
    }

    /**
     * Submit package
     */
    public function submit(User $user, Package $package): bool
    {
        return $user->hasRole('Staff');
    }

    /**
     * Approve package
     */
    public function approve(User $user, Package $package): bool
    {
        return $user->hasAnyRole(['Kabid', 'Admin', 'Super Admin']);
    }

    /**
     * Return package to draft
     */
    public function returnToDraft(User $user, Package $package): bool
    {
        return $user->hasAnyRole(['Kabid', 'Admin', 'Super Admin']);
    }
}
