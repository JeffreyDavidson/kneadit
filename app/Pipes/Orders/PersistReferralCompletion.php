<?php

namespace App\Pipes\Orders;

use App\Enums\Customers\CustomerReferralStatus;
use App\Events\Customers\CustomerReferralCompleted;
use App\Models\Customers\CustomerReferral;
use Closure;

/**
 * After the order has been persisted, record the referral relationship and
 * fire the completion event so the referrer can be rewarded.
 */
class PersistReferralCompletion
{
    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        if ($payload->referrer === null || $payload->customer === null || $payload->order === null) {
            return $next($payload);
        }

        $referral = CustomerReferral::query()->create([
            'referrer_customer_id' => $payload->referrer->id,
            'referred_customer_id' => $payload->customer->id,
            'order_id' => $payload->order->id,
            'status' => CustomerReferralStatus::Completed,
            'completed_at' => now(),
        ]);

        session()->forget('referral_code');

        event(new CustomerReferralCompleted($referral));

        return $next($payload);
    }
}
