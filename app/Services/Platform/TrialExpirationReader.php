<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Generator;

/**
 * Locates tenants in the trial-expiration funnel and resolves the user
 * account that owns each tenant. Free-forever tenants are never in the
 * funnel. The action decides eligibility — the reader only owns the queries.
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
            ->where('free_forever', false)
            ->lazyById();
    }

    /**
     * Tenants whose trial has expired and that are not paused yet, whether or
     * not they use a KneadIt storefront.
     *
     * @return Generator<int, Tenant>
     */
    public function tenantsExpired(): Generator
    {
        yield from Tenant::query()
            ->where('trial_ends_at', '<', now())
            ->whereNull('paused_at')
            ->where('free_forever', false)
            ->lazyById();
    }

    /**
     * Returns the user account that owns the tenant (tenants.user_id), or
     * null when the tenant has no linked owner. Matching by email is avoided
     * because an owner can change their login email.
     */
    public function userFor(Tenant $tenant): ?User
    {
        return $tenant->owner;
    }
}
