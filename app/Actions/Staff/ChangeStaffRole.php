<?php

namespace App\Actions\Staff;

use App\Enums\UserRole;
use App\Models\User;

class ChangeStaffRole
{
    public function __invoke(int $userId, UserRole $newRole, int $currentUserId): void
    {
        $user = User::query()->findOrFail($userId);

        throw_if($user->id === $currentUserId, \RuntimeException::class, "You can't change your own role.");

        $user->update(['role' => $newRole]);
    }
}
