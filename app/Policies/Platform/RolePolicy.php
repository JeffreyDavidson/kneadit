<?php

namespace App\Policies\Platform;

use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Auth\Access\Response;

abstract class RolePolicy
{
    protected UserRole $minimumRole = UserRole::Manager;

    public function viewAny(User $user): bool
    {
        return $user->role->meetsRequirement($this->minimumRole);
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->role->meetsRequirement($this->minimumRole);
    }

    public function create(User $user): bool
    {
        return $user->role->meetsRequirement($this->minimumRole);
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->role->meetsRequirement($this->minimumRole);
    }

    /**
     * Subclasses may return a denying Response so the reason reaches the user
     * (Filament shows it when a bulk delete skips a record).
     */
    public function delete(User $user, mixed $model): bool|Response
    {
        return $user->role->meetsRequirement($this->minimumRole);
    }
}
