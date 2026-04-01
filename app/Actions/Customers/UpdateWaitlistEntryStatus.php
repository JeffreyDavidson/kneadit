<?php

namespace App\Actions\Customers;

use App\Enums\Customers\WaitlistStatus;
use App\Models\Customers\WaitlistEntry;

class UpdateWaitlistEntryStatus
{
    public function __invoke(WaitlistEntry $entry, WaitlistStatus $status): void
    {
        $entry->update(['status' => $status]);
    }
}
