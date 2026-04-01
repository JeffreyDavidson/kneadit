<?php

namespace App\Actions\Stripe;

use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Log;

class SyncSubscriptionPlan
{
    /** @param array<string, string> $priceMap */
    public function __invoke(string $tenantEmail, string $stripePriceId, array $priceMap): void
    {
        $plan = $priceMap[$stripePriceId] ?? null;
        if (! $plan) {
            Log::warning('Unknown Stripe price ID', ['price_id' => $stripePriceId]);

            return;
        }

        $tenant = Tenant::query()->where('email', $tenantEmail)->first();
        if (! $tenant) {
            Log::warning('Tenant not found for subscription update', ['email' => $tenantEmail]);

            return;
        }

        $tenant->update(['plan' => $plan]);

        Log::info('Tenant plan updated from Stripe', [
            'tenant' => $tenant->id,
            'plan' => $plan,
        ]);
    }
}
