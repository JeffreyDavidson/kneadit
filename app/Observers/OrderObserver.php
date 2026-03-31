<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\GenerateOrderNumber;

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
