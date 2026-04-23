<?php

namespace App\Events\Customers;

use App\Models\Inventory\ProductWaitlist;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class ProductWaitlistJoined implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly ProductWaitlist $entry,
    ) {}
}
