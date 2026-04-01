<?php

namespace App\Queries\Platform;

use App\Models\Platform\Tenant;
use App\Models\Staff\User;

class StripeCustomerLookupQuery
{
    /**
     * Find the User and Tenant for a Stripe customer ID.
     *
     * @return array{user: User|null, tenant: Tenant|null}
     */
    public static function find(string $stripeCustomerId): array
    {
        $user = User::query()->where('stripe_id', $stripeCustomerId)->first();

        if (! $user) {
            return ['user' => null, 'tenant' => null];
        }

        $tenant = Tenant::query()->where('email', $user->email)->first();

        return ['user' => $user, 'tenant' => $tenant];
    }
}
