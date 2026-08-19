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

    public function unexpired(): static
    {
        $this->where('expires_at', '>', now());

        return $this;
    }

    public function pendingAndUnexpired(): static
    {
        return $this->pending()->unexpired();
    }
}
