<?php

namespace App\Policies;

use App\Models\TravelOrder;
use App\Models\User;

class TravelOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManage($user) || $user->hasRole('Staff');
    }

    public function view(User $user, TravelOrder $travelOrder): bool
    {
        return $this->canManage($user)
            || ($user->hasRole('Staff') && (int) $travelOrder->created_by === (int) $user->getKey());
    }

    public function update(User $user, TravelOrder $travelOrder): bool
    {
        return $user->hasRole('Staff')
            && (int) $travelOrder->created_by === (int) $user->getKey();
    }

    public function delete(User $user, TravelOrder $travelOrder): bool
    {
        return $this->update($user, $travelOrder);
    }

    private function canManage(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Super Admin', 'Kabid']);
    }
}
