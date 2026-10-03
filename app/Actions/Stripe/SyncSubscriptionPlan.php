<?php

declare(strict_types=1);

namespace App\Actions\Stripe;

use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Log;

class SyncSubscriptionPlan
{
    /** @param array<string, string> $priceMap */
    public function __invoke(Tenant $tenant, string $stripePriceId, array $priceMap): void
    {
        $plan = $priceMap[$stripePriceId] ?? null;
        if (! $plan) {
            Log::warning('Unknown Stripe price ID', ['price_id' => $stripePriceId]);

            return;
        }

        $tenant->update(['plan' => $plan]);

        Log::info('Tenant plan updated from Stripe', [
            'tenant' => $tenant->id,
            'plan' => $plan,
        ]);
    }
}
