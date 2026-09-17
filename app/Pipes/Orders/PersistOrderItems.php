<?php

namespace App\Pipes\Orders;

use App\Models\Orders\Order;
use Closure;

class PersistOrderItems
{
    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        assert($payload->order instanceof Order, 'Order must be persisted before PersistOrderItems');

        foreach ($payload->orderItems as $item) {
            $payload->order->orderItems()->create($item);
        }

        return $next($payload);
    }
}
