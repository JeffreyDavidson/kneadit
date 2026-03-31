<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class CampaignEmailQueued implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly string $email,
        public readonly string $subject,
        public readonly string $body,
    ) {}
}
