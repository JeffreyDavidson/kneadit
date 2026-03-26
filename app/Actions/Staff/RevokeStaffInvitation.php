<?php

namespace App\Actions\Staff;

use App\Models\StaffInvitation;

class RevokeStaffInvitation
{
    public function __invoke(int $invitationId): void
    {
        StaffInvitation::query()->where('id', $invitationId)->whereNull('accepted_at')->delete();
    }
}
