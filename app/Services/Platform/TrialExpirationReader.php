<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Generator;

/**
 * Locates tenants in the trial-expiration funnel and resolves the user
 * account that owns each tenant. The action decides eligibility — the
 * reader only owns the queries.
 */
class TrialExpirationReader
{
    /**
     * Tenants whose trial ends exactly N days from today.
     *
     * @return Generator<int, Tenant>
     */
    public function tenantsRemindable(int $daysLeft): Generator
    {
        $targetDate = now()->addDays($daysLeft)->startOfDay()->toDateString();

        yield from Tenant::query()
            ->whereDate('trial_ends_at', $targetDate)
            ->where('is_active', true)
            ->cursor();
    }

    /**
     * Tenants whose trial has expired and storefront is still enabled.
     *
     * @return Generator<int, Tenant>
     */
    public function tenantsExpired(): Generator
    {
        yield from Tenant::query()
            ->where('trial_ends_at', '<', now())
            ->where('is_active', true)
            ->where('storefront_enabled', true)
            ->cursor();
    }

    /**
     * Returns the user account that owns the tenant, or null if no
     * matching user exists.
     */
    public function userFor(Tenant $tenant): ?User
    {
        return User::query()->where('email', $tenant->email)->first();
    }
}
