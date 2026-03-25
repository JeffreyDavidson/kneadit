<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Ingredient;
use App\Models\User;

class IngredientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, Ingredient $ingredient): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, Ingredient $ingredient): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, Ingredient $ingredient): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
