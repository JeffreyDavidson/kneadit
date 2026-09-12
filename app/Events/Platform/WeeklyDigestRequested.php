<?php

namespace App\Events\Platform;

use App\DataTransferObjects\Platform\WeeklyDigestData;
use App\Models\Staff\User;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class WeeklyDigestRequested implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly WeeklyDigestData $data,
    ) {}
}
