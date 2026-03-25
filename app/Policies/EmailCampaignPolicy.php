<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\EmailCampaign;
use App\Models\User;

class EmailCampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function view(User $user, EmailCampaign $emailCampaign): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function update(User $user, EmailCampaign $emailCampaign): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }

    public function delete(User $user, EmailCampaign $emailCampaign): bool
    {
        return $user->hasMinRole(UserRole::Manager);
    }
}
