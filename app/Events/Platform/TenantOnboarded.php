<?php

namespace App\Events\Platform;

use App\Models\Tenant;
use App\Models\User;
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
