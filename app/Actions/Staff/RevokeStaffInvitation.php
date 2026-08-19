<?php

namespace App\Actions\Staff;

use App\Models\Staff\StaffInvitation;

class RevokeStaffInvitation
{
    public function __invoke(int $invitationId): void
    {
        StaffInvitation::query()->pending()->whereKey($invitationId)->delete();
    }
}
