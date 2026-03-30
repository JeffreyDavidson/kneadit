<?php

namespace App\Actions\Staff;

use App\Enums\UserRole;
use App\Models\User;

class RemoveStaffMember
{
    public function __invoke(int $userId, int $currentUserId): void
    {
        $user = User::query()->findOrFail($userId);

        throw_if($user->id === $currentUserId, \RuntimeException::class, "You can't remove yourself.");

        throw_if($user->isOwner() && User::query()->where('role', UserRole::Owner)->count() <= 1, \RuntimeException::class, "Can't remove the last owner.");

        $user->delete();
    }
}
