<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Recipe;
use App\Models\User;

class RecipePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, Recipe $recipe): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, Recipe $recipe): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, Recipe $recipe): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
