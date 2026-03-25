<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\LoyaltyReward;
use App\Models\User;

class LoyaltyRewardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, LoyaltyReward $loyaltyReward): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, LoyaltyReward $loyaltyReward): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, LoyaltyReward $loyaltyReward): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
