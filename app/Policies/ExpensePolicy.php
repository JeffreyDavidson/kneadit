<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
