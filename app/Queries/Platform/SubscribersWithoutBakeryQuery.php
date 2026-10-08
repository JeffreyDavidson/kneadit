<?php

declare(strict_types=1);

namespace App\Queries\Platform;

use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Stripe\Subscription as StripeSubscription;

/**
 * Finds platform users who pay (or are about to pay) for a plan but have no
 * bakery linked to them through tenants.user_id.
 */
final class SubscribersWithoutBakeryQuery
{
    /** @var array<int, string> */
    private const array BILLING_STATUSES = [
        StripeSubscription::STATUS_ACTIVE,
        StripeSubscription::STATUS_TRIALING,
        StripeSubscription::STATUS_PAST_DUE,
    ];

    /** @return Collection<int, int> The user ids, oldest first. */
    public function ids(): Collection
    {
        return User::query()
            ->whereDoesntHave('tenants')
            ->whereHas('subscriptions', fn (Builder $subscriptions) => $subscriptions
                ->where('type', 'default')
                ->whereIn('stripe_status', self::BILLING_STATUSES))
            ->orderBy('id')
            ->get(['id'])
            ->map(fn (User $user): int => $user->id)
            ->values();
    }
}
