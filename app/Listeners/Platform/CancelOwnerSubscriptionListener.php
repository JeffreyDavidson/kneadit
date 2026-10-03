<?php

namespace App\Listeners\Platform;

use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Laravel\Cashier\Subscription;
use Stancl\Tenancy\Events\DeletingTenant;

/**
 * Stops billing the owner when their bakery is deleted. Runs before the
 * delete, so a failed Stripe call stops the bakery from being deleted.
 *
 * The owner is matched by email, as TrialExpirationReader::userFor() does,
 * because tenants.user_id is not populated at signup.
 */
class CancelOwnerSubscriptionListener
{
    public function handle(DeletingTenant $event): void
    {
        /** @var Tenant $tenant */
        $tenant = $event->tenant;

        $owner = User::query()->where('email', $tenant->email)->first();

        if (! $owner instanceof User) {
            return;
        }

        $hasAnotherBakery = Tenant::query()
            ->where('email', $tenant->email)
            ->whereKeyNot($tenant->getTenantKey())
            ->exists();

        if ($hasAnotherBakery) {
            return;
        }

        $subscription = $owner->subscription('default');

        if (! $subscription instanceof Subscription || ! $subscription->valid() || $subscription->canceled()) {
            return;
        }

        $subscription->cancel();
    }
}
