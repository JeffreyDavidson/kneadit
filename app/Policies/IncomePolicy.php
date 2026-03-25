<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Income;
use App\Models\User;

class IncomePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, Income $income): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, Income $income): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, Income $income): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
