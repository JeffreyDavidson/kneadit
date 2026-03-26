<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    public function creating(Order $order): void
    {
        if (! $order->order_number) {
            $order->order_number = 'ORD-' . str_pad((string) (Order::query()->count() + 1), 6, '0', STR_PAD_LEFT);
        }
    }
}
