<?php

namespace App\Pipes\Orders;

use Closure;

class PersistOrderItems
{
    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        assert($payload->order !== null, 'Order must be persisted before PersistOrderItems');

        foreach ($payload->orderItems as $item) {
            $payload->order->orderItems()->create($item);
        }

        return $next($payload);
    }
}
