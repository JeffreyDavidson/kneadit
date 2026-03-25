<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
