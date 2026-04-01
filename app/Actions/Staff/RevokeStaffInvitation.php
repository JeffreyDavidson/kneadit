<?php

namespace App\Actions\Staff;

use App\Models\Staff\StaffInvitation;

class RevokeStaffInvitation
{
    public function __invoke(int $invitationId): void
    {
        StaffInvitation::query()->where('id', $invitationId)->whereNull('accepted_at')->delete();
    }
}
