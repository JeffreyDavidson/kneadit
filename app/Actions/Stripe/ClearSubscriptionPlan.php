<?php

declare(strict_types=1);

namespace App\Actions\Stripe;

use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Log;

/**
 * Takes a bakery back to the plan every new bakery starts on once its paid
 * subscription has ended. tenants.plan is not nullable, so this is the
 * "no paid plan" value; whether the bakery stays open is decided by the
 * trial-expiry run, which pauses it.
 */
class ClearSubscriptionPlan
{
    public function __invoke(Tenant $tenant): void
    {
        if ($tenant->plan === SubscriptionTier::Starter) {
            return;
        }

        $tenant->update(['plan' => SubscriptionTier::Starter]);

        Log::info('Tenant plan reset after its subscription ended', ['tenant' => $tenant->id]);
    }
}
