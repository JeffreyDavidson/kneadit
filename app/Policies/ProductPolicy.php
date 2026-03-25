<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }
}
