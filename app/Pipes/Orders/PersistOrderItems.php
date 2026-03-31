<?php

namespace App\Pipes\Orders;

use Closure;

class PersistOrderItems
{
    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        foreach ($payload->orderItems as $item) {
            $payload->order->orderItems()->create($item);
        }

        return $next($payload);
    }
}
