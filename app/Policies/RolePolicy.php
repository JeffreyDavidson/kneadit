<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

abstract class RolePolicy
{
    protected UserRole $minimumRole = UserRole::Manager;

    public function viewAny(User $user): bool
    {
        return $user->hasMinRole($this->minimumRole);
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->hasMinRole($this->minimumRole);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole($this->minimumRole);
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->hasMinRole($this->minimumRole);
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->hasMinRole($this->minimumRole);
    }
}
