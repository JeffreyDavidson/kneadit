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
 * Runs synchronously on purpose: a queued job would run after the tenant
 * is gone, and could not stop the delete.
 *
 * The owner is the bakery's linked account (tenants.user_id). A bakery with
 * no linked owner cancels nothing. An owner has at most one bakery (unique
 * index), so cancelling never affects another bakery.
 */
class CancelOwnerSubscriptionListener
{
    public function handle(DeletingTenant $event): void
    {
        /** @var Tenant $tenant */
        $tenant = $event->tenant;

        // The relation inherits the tenant's central connection, so this stays
        // correct if the tenant is deleted while tenancy is initialized.
        $owner = $tenant->owner;

        if (! $owner instanceof User) {
            return;
        }

        $subscription = $owner->subscription('default');

        if (! $subscription instanceof Subscription || ! $subscription->valid() || $subscription->canceled()) {
            return;
        }

        $subscription->cancel();
    }
}
