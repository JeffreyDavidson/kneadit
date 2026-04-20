<?php

namespace App\Builders\Platform;

use App\Enums\Platform\SupportTicketStatus;
use App\Models\Platform\SupportTicket;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<SupportTicket> */
class SupportTicketQueryBuilder extends Builder
{
    public function open(): static
    {
        $this->where('status', SupportTicketStatus::Open);

        return $this;
    }
}
