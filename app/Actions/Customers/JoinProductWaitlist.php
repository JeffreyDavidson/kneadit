<?php

namespace App\Actions\Customers;

use App\Events\Customers\ProductWaitlistJoined;
use App\Models\Inventory\ProductWaitlist;

class JoinProductWaitlist
{
    public function __invoke(int $productId, string $customerEmail, ?string $customerName = null): ProductWaitlist
    {
        $entry = ProductWaitlist::query()->updateOrCreate([
            'product_id' => $productId,
            'customer_email' => $customerEmail,
        ], [
            'customer_name' => $customerName,
            'notified_at' => null,
            'created_at' => now(),
        ]);

        // Only notify on first join — silent when the customer rejoins
        // an existing waitlist (e.g. clicks the button again).
        if ($entry->wasRecentlyCreated) {
            event(new ProductWaitlistJoined($entry));
        }

        return $entry;
    }
}
