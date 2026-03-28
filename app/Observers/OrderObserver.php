<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class OrderObserver
{
    public function creating(Order $order): void
    {
        if (! $order->order_number) {
            $order->order_number = Cache::lock('order_number_lock', 5)->block(5, function () {
                $maxNumber = Order::query()
                    ->where('order_number', 'like', 'ORD-%')
                    ->selectRaw('MAX(CAST(SUBSTR(order_number, 5) AS INTEGER)) as max_num')
                    ->value('max_num') ?? 0;

                return 'ORD-' . str_pad((string) ($maxNumber + 1), 6, '0', STR_PAD_LEFT);
            });
        }
    }
}
