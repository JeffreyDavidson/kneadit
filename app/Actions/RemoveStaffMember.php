<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;

class RemoveStaffMember
{
    public function __invoke(int $userId, int $currentUserId): void
    {
        $user = User::query()->findOrFail($userId);

        if ($user->id === $currentUserId) {
            throw new \RuntimeException("You can't remove yourself.");
        }

        if ($user->isOwner() && User::query()->where('role', UserRole::Owner)->count() <= 1) {
            throw new \RuntimeException("Can't remove the last owner.");
        }

        $user->delete();
    }
}
