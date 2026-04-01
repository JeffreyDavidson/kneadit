<?php

namespace App\Observers\Orders;

use App\Actions\Orders\GenerateOrderNumber;
use App\Models\Orders\Order;

class OrderObserver
{
    public function __construct(
        private GenerateOrderNumber $generateOrderNumber,
    ) {}

    public function creating(Order $order): void
    {
        if (! $order->order_number) {
            $order->order_number = ($this->generateOrderNumber)();
        }
    }
}
