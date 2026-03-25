<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\GiftCard;
use App\Models\User;

class GiftCardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, GiftCard $giftCard): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, GiftCard $giftCard): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, GiftCard $giftCard): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
