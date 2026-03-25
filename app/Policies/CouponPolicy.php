<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Coupon;
use App\Models\User;

class CouponPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, Coupon $coupon): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, Coupon $coupon): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
