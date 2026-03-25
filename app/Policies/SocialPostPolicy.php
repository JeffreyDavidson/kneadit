<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\SocialPost;
use App\Models\User;

class SocialPostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, SocialPost $socialPost): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, SocialPost $socialPost): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, SocialPost $socialPost): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
