<?php

namespace App\Actions\Customers;

use App\Models\Inventory\ProductWaitlist;

class JoinProductWaitlist
{
    public function __invoke(int $productId, string $customerEmail, ?string $customerName = null): ProductWaitlist
    {
        return ProductWaitlist::query()->updateOrCreate([
            'product_id' => $productId,
            'customer_email' => $customerEmail,
        ], [
            'customer_name' => $customerName,
            'notified_at' => null,
            'created_at' => now(),
        ]);
    }
}
