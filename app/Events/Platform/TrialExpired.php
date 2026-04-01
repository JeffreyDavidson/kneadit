<?php

namespace App\Events\Platform;

use App\Models\Staff\User;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class TrialExpired implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly string $tenantId,
    ) {}
}
