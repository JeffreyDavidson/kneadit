<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }
}
