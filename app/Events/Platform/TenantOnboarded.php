<?php

namespace App\Events\Platform;

use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class TenantOnboarded implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly Tenant $tenant,
        public readonly string $adminUrl,
    ) {}
}
