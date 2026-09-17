<?php

declare(strict_types=1);

namespace App\Events\Customers;

use App\Models\Orders\Order;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class ReviewRequested implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly Order $order,
    ) {}
}
