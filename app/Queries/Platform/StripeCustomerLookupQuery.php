<?php

namespace App\Queries\Platform;

use App\Models\Platform\Tenant;
use App\Models\Staff\User;

class StripeCustomerLookupQuery
{
    /**
     * Find the User and the bakery they own (tenants.user_id) for a Stripe
     * customer ID. A user owns at most one bakery.
     *
     * @return array{user: User|null, tenant: Tenant|null}
     */
    public function find(string $stripeCustomerId): array
    {
        $user = User::query()->where('stripe_id', $stripeCustomerId)->first();

        if (! $user) {
            return ['user' => null, 'tenant' => null];
        }

        return ['user' => $user, 'tenant' => $user->tenants()->first()];
    }
}
