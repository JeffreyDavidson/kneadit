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
 * The owner is still matched by email. Signup now records tenants.user_id
 * (see Tenant::owner()), so this lookup can move to it in a follow-up.
 */
class CancelOwnerSubscriptionListener
{
    public function handle(DeletingTenant $event): void
    {
        /** @var Tenant $tenant */
        $tenant = $event->tenant;

        // Staff users share this model, so pin the lookup to the central
        // database in case the tenant is deleted while tenancy is initialized.
        $owner = User::on('central')->where('email', $tenant->email)->first();

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
