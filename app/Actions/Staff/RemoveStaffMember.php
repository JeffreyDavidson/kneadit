<?php

namespace App\Actions\Staff;

use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Support\Facades\DB;

class RemoveStaffMember
{
    public function __invoke(int $userId, int $currentUserId): void
    {
        $user = User::query()->findOrFail($userId);

        throw_if($user->id === $currentUserId, \RuntimeException::class, "You can't remove yourself.");

        $this->guardLastOwner($user, User::query()->owners()->count());

        // Two owners removing each other at once could both pass the count
        // above, so lock the owner rows and count again before deleting.
        DB::transaction(function () use ($userId): void {
            $user = User::query()->lockForUpdate()->findOrFail($userId);

            $this->guardLastOwner($user, User::query()->owners()->lockForUpdate()->count());

            $user->delete();
        });
    }

    private function guardLastOwner(User $user, int $ownerCount): void
    {
        throw_if($user->role === UserRole::Owner && $ownerCount <= 1, \RuntimeException::class, "Can't remove the last owner.");
    }
}
