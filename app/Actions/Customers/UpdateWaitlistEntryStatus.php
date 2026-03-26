<?php

namespace App\Actions\Customers;

use App\Enums\WaitlistStatus;
use App\Models\WaitlistEntry;

class UpdateWaitlistEntryStatus
{
    public function __invoke(WaitlistEntry $entry, WaitlistStatus $status): void
    {
        $entry->update(['status' => $status]);
    }
}
