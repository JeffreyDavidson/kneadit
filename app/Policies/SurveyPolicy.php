<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Survey;
use App\Models\User;

class SurveyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, Survey $survey): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, Survey $survey): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, Survey $survey): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
