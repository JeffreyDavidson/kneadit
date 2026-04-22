<?php

namespace App\Builders\Staff;

use App\Models\Staff\StaffInvitation;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<StaffInvitation> */
class StaffInvitationQueryBuilder extends Builder
{
    public function pending(): static
    {
        $this->whereNull('accepted_at');

        return $this;
    }
}
