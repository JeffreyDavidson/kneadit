<?php

namespace App\Actions\Staff;

use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;

class AcceptStaffInvitation
{
    public function __invoke(StaffInvitation $invitation, ?string $name = null, ?string $password = null): User
    {
        $existingUser = User::query()->where('email', $invitation->email)->first();

        if ($existingUser) {
            $existingUser->update(['role' => $invitation->role]);
            $user = $existingUser;
        } else {
            $user = User::query()->create([
                'name' => $name,
                'email' => $invitation->email,
                'password' => $password,
                'role' => $invitation->role,
                'email_verified_at' => now(),
            ]);
        }

        $invitation->update(['accepted_at' => now()]);

        return $user;
    }
}
