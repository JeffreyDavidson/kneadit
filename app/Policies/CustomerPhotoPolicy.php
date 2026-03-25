<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CustomerPhoto;
use App\Models\User;

class CustomerPhotoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, CustomerPhoto $customerPhoto): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, CustomerPhoto $customerPhoto): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, CustomerPhoto $customerPhoto): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
