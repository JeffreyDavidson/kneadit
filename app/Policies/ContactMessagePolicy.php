<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ContactMessage;
use App\Models\User;

class ContactMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, ContactMessage $contactMessage): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, ContactMessage $contactMessage): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, ContactMessage $contactMessage): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
