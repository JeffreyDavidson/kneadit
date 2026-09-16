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
        public readonly ?string $bakerName = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $adminUrl = null,
        public readonly ?string $helpUrl = null,
    ) {}
}
