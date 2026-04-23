<?php

namespace App\Pipes\Orders;

use App\Services\Loyalty\CustomerLoyalty;
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

        if ($payload->deliveryFee > 0 && $this->customerLoyalty->qualifiesForFreeDelivery($payload->customer)) {
            $payload->deliveryFee = 0;

            $afterDiscount = max(0.0, $payload->subtotal + $payload->deliveryFee - $payload->discountAmount);
            $payload->total = max(0.0, $afterDiscount - $payload->giftCardAmount) + $payload->tipAmount;
        }

        return $next($payload);
    }
}
