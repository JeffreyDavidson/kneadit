<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CapacityLimit;
use App\Models\User;

class CapacityLimitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, CapacityLimit $capacityLimit): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, CapacityLimit $capacityLimit): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, CapacityLimit $capacityLimit): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
