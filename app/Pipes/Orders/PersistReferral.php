<?php

namespace App\Pipes\Orders;

use App\Enums\Customers\CustomerReferralStatus;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReferral;
use App\Models\Orders\Order;
use Closure;

/**
 * After the order has been persisted, record the referral relationship as
 * pending. It is completed (and the referrer rewarded) when the order is
 * delivered, or cancelled if the order is.
 */
class PersistReferral
{
    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        if (! $payload->referrer instanceof Customer || ! $payload->customer instanceof Customer || ! $payload->order instanceof Order) {
            return $next($payload);
        }

        CustomerReferral::query()->create([
            'referrer_customer_id' => $payload->referrer->id,
            'referred_customer_id' => $payload->customer->id,
            'order_id' => $payload->order->id,
            'status' => CustomerReferralStatus::Pending,
        ]);

        session()->forget('referral_code');

        return $next($payload);
    }
}
