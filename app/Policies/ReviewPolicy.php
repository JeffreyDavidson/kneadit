<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, Review $review): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, Review $review): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
