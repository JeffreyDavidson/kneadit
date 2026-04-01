<?php

namespace App\Actions\Orders;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class GenerateOrderNumber
{
    public function __invoke(): string
    {
        return Cache::lock('order_number_lock', 5)->block(5, function () {
            $maxNumber = Order::query()
                ->whereLike('order_number', 'ORD-%')
                ->selectRaw('MAX(CAST(SUBSTR(order_number, 5) AS INTEGER)) as max_num')
                ->value('max_num') ?? 0;

            return 'ORD-' . str_pad((string) ($maxNumber + 1), 6, '0', STR_PAD_LEFT);
        });
    }
}
