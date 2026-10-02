<?php

declare(strict_types=1);

namespace App\Listeners\Customers;

use App\Actions\Inventory\NotifyProductWaitlist;
use App\Events\Customers\ProductReactivated;
use App\Listeners\QueuedListener;

/**
 * Tells customers on a product's waitlist that it is back. Runs queued, inside
 * the tenant that dispatched it, so the action resolves that tenant's settings.
 */
class NotifyProductWaitlistListener extends QueuedListener
{
    public function handle(ProductReactivated $event): void
    {
        resolve(NotifyProductWaitlist::class)($event->product);
    }
}
