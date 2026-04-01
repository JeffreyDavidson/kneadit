<?php

namespace App\Actions\Customers;

use App\Enums\Customers\WaitlistStatus;
use App\Models\Customers\WaitlistEntry;

class JoinWaitlist
{
    /** @param array<string, mixed> $data */
    public function __invoke(array $data): WaitlistEntry
    {
        return WaitlistEntry::query()->create([
            ...$data,
            'status' => WaitlistStatus::Waiting,
        ]);
    }
}
