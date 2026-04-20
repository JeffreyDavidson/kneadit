<?php

namespace App\Builders\Customers;

use App\Enums\Customers\WaitlistStatus;
use App\Models\Customers\WaitlistEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

/** @extends Builder<WaitlistEntry> */
class WaitlistEntryQueryBuilder extends Builder
{
    public function waiting(): static
    {
        $this->where('status', WaitlistStatus::Waiting);

        return $this;
    }

    public function forDate(Carbon|string $date): static
    {
        $this->whereDate('requested_date', Date::parse($date));

        return $this;
    }
}
