<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;

class ChangeStaffRole
{
    public function __invoke(int $userId, UserRole $newRole, int $currentUserId): void
    {
        $user = User::query()->findOrFail($userId);

        if ($user->id === $currentUserId) {
            throw new \RuntimeException("You can't change your own role.");
        }

        $user->update(['role' => $newRole]);
    }
}
