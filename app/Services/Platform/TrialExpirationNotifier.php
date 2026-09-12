<?php

namespace App\Services\Platform;

use App\Events\Platform\TrialExpired;
use App\Events\Platform\TrialReminding;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Support\Facades\Log;

/**
 * Wraps event dispatch for the trial-expiration flow with consistent
 * try/catch + structured logging. Returns success booleans so the
 * caller can update counters without managing exception state.
 */
class TrialExpirationNotifier
{
    public function __construct(private readonly TenantUrlGenerator $tenantUrls) {}

    public function sendReminder(User $user, Tenant $tenant, int $daysLeft): bool
    {
        try {
            $storeName = $tenant->store_name ?: $tenant->name;
            event(new TrialReminding($user, $storeName, $daysLeft));

            return true;
        } catch (\Exception $e) {
            Log::error('Trial reminder dispatch failed', [
                'email' => $user->email,
                'tenant' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function notifyExpired(User $user, Tenant $tenant): void
    {
        try {
            event(new TrialExpired(
                user: $user,
                tenantId: $tenant->id,
                adminUrl: $this->tenantUrls->admin($tenant),
            ));
        } catch (\Exception $e) {
            Log::error('Trial expiration email dispatch failed', [
                'tenant' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
