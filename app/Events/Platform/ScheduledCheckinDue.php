<?php

namespace App\Events\Platform;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class ScheduledCheckinDue implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly string $tenantEmail,
        public readonly string $body,
        public readonly string $subject,
    ) {}
}
