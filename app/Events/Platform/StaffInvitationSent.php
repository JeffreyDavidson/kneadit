<?php

namespace App\Events\Platform;

use App\Models\StaffInvitation;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class StaffInvitationSent implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly StaffInvitation $invitation,
        public readonly string $storeName,
        public readonly string $acceptUrl,
    ) {}
}
