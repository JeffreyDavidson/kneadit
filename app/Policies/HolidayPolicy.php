<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Holiday;
use App\Models\User;

class HolidayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, Holiday $holiday): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, Holiday $holiday): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, Holiday $holiday): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
