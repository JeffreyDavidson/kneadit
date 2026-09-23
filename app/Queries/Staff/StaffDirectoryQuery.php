<?php

namespace App\Queries\Staff;

use App\Enums\Staff\UserRole;
use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Collection;

class StaffDirectoryQuery
{
    public function member(int $id): User
    {
        return User::query()->findOrFail($id);
    }

    /** @return Collection<int, User> */
    public function members(): Collection
    {
        return User::query()
            ->orderByRaw('CASE role WHEN ? THEN 1 WHEN ? THEN 2 WHEN ? THEN 3 ELSE 4 END', [
                UserRole::Owner->value,
                UserRole::Manager->value,
                UserRole::Staff->value,
            ])
            ->get();
    }

    /** @return Collection<int, StaffInvitation> */
    public function pendingInvitations(): Collection
    {
        return StaffInvitation::query()
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->get();
    }
}
