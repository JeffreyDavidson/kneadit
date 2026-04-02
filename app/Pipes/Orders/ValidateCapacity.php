<?php

namespace App\Pipes\Orders;

use App\Exceptions\Orders\CapacityExceededException;
use App\Services\Inventory\InventoryManager;
use Closure;

class ValidateCapacity
{
    public function __construct(
        private InventoryManager $inventory,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        try {
            $this->inventory->guardCapacity($payload->data->deliveryDate);
        } catch (CapacityExceededException) {
            $payload->cancelled = true;

            return $payload;
        }

        return $next($payload);
    }
}
