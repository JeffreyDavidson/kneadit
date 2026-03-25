<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\BlockedDate;
use App\Models\User;

class BlockedDatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, BlockedDate $blockedDate): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, BlockedDate $blockedDate): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, BlockedDate $blockedDate): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
