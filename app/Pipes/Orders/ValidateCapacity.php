<?php

namespace App\Pipes\Orders;

use App\Services\Inventory\CapacityCalculator;
use Closure;

class ValidateCapacity
{
    public function __construct(
        private CapacityCalculator $calculator,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        if (! $this->calculator->isAvailable($payload->data->deliveryDate)) {
            $payload->cancelled = true;

            return $payload;
        }

        return $next($payload);
    }
}
