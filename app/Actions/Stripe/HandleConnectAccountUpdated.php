<?php

namespace App\Actions\Stripe;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class HandleConnectAccountUpdated
{
    public function __invoke(mixed $account): void
    {
        $accountId = data_get($account, 'id');
        $chargesEnabled = (bool) data_get($account, 'charges_enabled', false);
        $tenantId = is_object($account)
            ? ($account->metadata->tenant_id ?? null)
            : ($account['metadata']['tenant_id'] ?? null);

        if (! $tenantId) {
            Log::warning('Stripe Connect account.updated missing tenant_id', ['account_id' => $accountId]);

            return;
        }

        Log::info('Stripe Connect account updated', [
            'account_id' => $accountId,
            'tenant_id' => $tenantId,
            'charges_enabled' => $chargesEnabled,
        ]);

        $tenant = Tenant::query()->find($tenantId);
        if (! $tenant) {
            Log::warning('Tenant not found for Stripe Connect update', ['tenant_id' => $tenantId]);

            return;
        }

        try {
            tenancy()->initialize($tenant);
            settings(['stripe_connect_charges_enabled' => $chargesEnabled ? '1' : '0']);

            if ($chargesEnabled) {
                Log::info('Stripe Connect fully enabled for tenant', ['tenant_id' => $tenantId]);
            }

            tenancy()->end();
        } catch (\Exception $e) {
            Log::error('Failed to update tenant Stripe Connect status', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
