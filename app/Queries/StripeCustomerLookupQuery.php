<?php

namespace App\Queries;

use App\Models\Tenant;
use App\Models\User;

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
