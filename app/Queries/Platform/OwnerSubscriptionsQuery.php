<?php

declare(strict_types=1);

namespace App\Queries\Platform;

use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Laravel\Cashier\Subscription;
use Stripe\Subscription as StripeSubscription;

/**
 * Reads an owner's platform ("default") Cashier subscriptions. Cashier's
 * subscription('default') only returns the newest row whatever its status, so
 * anything that must see every subscription that can still bill goes here.
 */
final class OwnerSubscriptionsQuery
{
    /**
     * Statuses that are over for good. Every other status, such as past_due,
     * incomplete or unpaid, is a subscription Stripe may still collect on.
     */
    private const array ENDED_STATUSES = [
        StripeSubscription::STATUS_CANCELED,
        StripeSubscription::STATUS_INCOMPLETE_EXPIRED,
    ];

    /**
     * Every default subscription that has not ended.
     *
     * @return Collection<int, Subscription>
     */
    public function open(User $owner): Collection
    {
        return $this->forOwner($owner)
            ->whereNotIn('stripe_status', self::ENDED_STATUSES)
            ->get();
    }

    /** Whether the owner has ever had a default subscription, in any status. */
    public function hasEverSubscribed(User $owner): bool
    {
        return $this->forOwner($owner)->exists();
    }

    /**
     * Whether a renewal failed within the grace period. Cashier updates the
     * row when Stripe moves it to past_due, so updated_at is when the first
     * failed renewal happened.
     */
    public function inPastDueGrace(User $owner): bool
    {
        return $this->forOwner($owner)
            ->where('stripe_status', StripeSubscription::STATUS_PAST_DUE)
            ->where('updated_at', '>', now()->subDays(Config::integer('kneadit.past_due_grace_days')))
            ->exists();
    }

    /**
     * Goes through the owner's relation so the subscriptions use the model
     * Cashier is configured with, and know their owner when they call Stripe.
     *
     * @return HasMany<Subscription, User>
     */
    private function forOwner(User $owner): HasMany
    {
        return $owner->subscriptions()
            ->chaperone('owner')
            ->where('type', 'default');
    }
}
