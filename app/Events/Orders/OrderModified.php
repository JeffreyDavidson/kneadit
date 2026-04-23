<?php

namespace App\Events\Orders;

use App\Models\Orders\Order;
use App\ValueObjects\Money;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class OrderModified implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly Order $order,
        public readonly Money $previousSubtotal,
        public readonly Money $previousTotal,
    ) {}
}
