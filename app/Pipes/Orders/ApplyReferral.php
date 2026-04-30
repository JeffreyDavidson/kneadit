<?php

namespace App\Pipes\Orders;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReferral;
use App\Services\Settings\TenantSettings;
use Closure;

/**
 * If a referral code is in the session and the program is enabled, find the
 * referrer, apply the configured discount to the order, and stash the
 * referrer on the payload so PersistReferralCompletion can record it later.
 *
 * Silently skips on any validation failure — referral is a perk, not a
 * blocker for the order itself.
 */
class ApplyReferral
{
    public function __construct(
        private TenantSettings $settings,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        if (! $this->settings->engagement->customerReferralProgramEnabled) {
            return $next($payload);
        }

        $code = session('referral_code');
        if (! is_string($code) || $code === '') {
            return $next($payload);
        }

        $referrer = Customer::query()->forReferralCode($code)->first();
        if (! $referrer) {
            return $next($payload);
        }

        if (strcasecmp($referrer->email, $payload->data->customerEmail) === 0) {
            return $next($payload);
        }

        $existingCustomer = Customer::query()->forEmail($payload->data->customerEmail)->first();
        if ($existingCustomer && CustomerReferral::query()->where('referred_customer_id', $existingCustomer->id)->exists()) {
            return $next($payload);
        }

        $discount = (float) $this->settings->engagement->customerReferralDiscountDollars;
        if ($discount <= 0) {
            return $next($payload);
        }

        $payload->referrer = $referrer;
        $payload->discountAmount += $discount;

        $afterDiscount = max(0.0, $payload->subtotal + $payload->deliveryFee - $payload->discountAmount);
        $payload->total = max(0.0, $afterDiscount - $payload->giftCardAmount) + $payload->tipAmount;

        return $next($payload);
    }
}
