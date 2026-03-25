<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
