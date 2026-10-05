<?php

namespace App\Actions\Staff;

use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Support\Facades\DB;

class ChangeStaffRole
{
    public function __invoke(int $userId, UserRole $newRole, int $currentUserId): void
    {
        $user = User::query()->findOrFail($userId);

        throw_if($user->id === $currentUserId, \RuntimeException::class, "You can't change your own role.");

        $this->guardLastOwner($user, $newRole, User::query()->owners()->count());

        // Two owners demoting each other at once could both pass the count
        // above, so lock the owner rows and count again before updating.
        DB::transaction(function () use ($userId, $newRole): void {
            $user = User::query()->lockForUpdate()->findOrFail($userId);

            $this->guardLastOwner($user, $newRole, User::query()->owners()->lockForUpdate()->count());

            $user->update(['role' => $newRole]);
        });
    }

    private function guardLastOwner(User $user, UserRole $newRole, int $ownerCount): void
    {
        throw_if(
            $user->role === UserRole::Owner && $newRole !== UserRole::Owner && $ownerCount <= 1,
            \RuntimeException::class,
            "Can't demote the last owner.",
        );
    }
}
