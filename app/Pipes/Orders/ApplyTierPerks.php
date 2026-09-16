<?php

namespace App\Pipes\Orders;

use App\Services\Loyalty\CustomerLoyalty;
use App\ValueObjects\Money;
use Closure;

/**
 * Applies loyalty-tier-driven perks (currently: free delivery) to the order
 * once the customer has been resolved. Skips silently if perks are disabled
 * or the customer's tier doesn't qualify.
 */
class ApplyTierPerks
{
    public function __construct(
        private CustomerLoyalty $customerLoyalty,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        if ($payload->customer === null) {
            return $next($payload);
        }

        if ($payload->deliveryFee->isPositive() && $this->customerLoyalty->qualifiesForFreeDelivery($payload->customer)) {
            $payload->deliveryFee = Money::zero();
            $payload->recalculateTotal();
        }

        return $next($payload);
    }
}
