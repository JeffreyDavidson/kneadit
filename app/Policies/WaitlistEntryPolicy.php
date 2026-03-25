<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\WaitlistEntry;

class WaitlistEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, WaitlistEntry $waitlistEntry): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, WaitlistEntry $waitlistEntry): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, WaitlistEntry $waitlistEntry): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
