<?php

declare(strict_types=1);

namespace App\Actions\Platform;

use App\Models\Staff\User;
use App\Queries\Platform\OwnerSubscriptionsQuery;

/**
 * Stops billing an owner: every platform subscription that has not ended is
 * cancelled, not only the newest. A healthy one runs to the end of the period
 * it already paid for; a past-due, incomplete or unpaid one has nothing paid
 * to run to and Stripe would keep collecting on it, so it ends now.
 */
class CancelOwnerSubscriptions
{
    public function __construct(private readonly OwnerSubscriptionsQuery $subscriptions) {}

    public function __invoke(User $owner): void
    {
        foreach ($this->subscriptions->open($owner) as $subscription) {
            if ($subscription->canceled()) {
                continue;
            }

            if ($subscription->valid()) {
                $subscription->cancel();

                continue;
            }

            $subscription->cancelNow();
        }
    }
}
