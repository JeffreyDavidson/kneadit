<?php

namespace App\Actions\Stripe;

use App\DataTransferObjects\Stripe\StripePromotionCodeResult;
use InvalidArgumentException;
use Stripe\StripeClient;

/**
 * Creates a Stripe Coupon + PromotionCode pair in one call and returns
 * the PromotionCode (the customer-facing redeemable resource). The two
 * are distinct in Stripe: the Coupon is the discount spec (how much off,
 * for how long), the PromotionCode is the human-typable string that
 * references a coupon.
 *
 * For the typical "one-time onboarding discount for a specific baker"
 * use case, the caller specifies:
 * - percentOff: 0..100 (integer) OR amountOffCents: unsigned int
 * - duration: 'once' | 'repeating' | 'forever'
 * - durationInMonths: required when duration='repeating'
 * - code: the string the customer will type at checkout (auto-generated
 *   by Stripe when omitted — pass one for a memorable code)
 * - maxRedemptions: defaults to 1 (single-use)
 * - expiresInDays: optional wall-clock expiry for the promotion code
 * - tenantId: stamped into the Stripe coupon metadata for later reconciliation
 */
class CreateStripePromotionCode
{
    public function __construct(
        private StripeClient $stripe,
    ) {}

    public function __invoke(
        ?int $percentOff = null,
        ?int $amountOffCents = null,
        string $duration = 'once',
        ?int $durationInMonths = null,
        ?string $code = null,
        int $maxRedemptions = 1,
        ?int $expiresInDays = null,
        ?string $tenantId = null,
        ?string $name = null,
    ): StripePromotionCodeResult {
        if ($percentOff === null && $amountOffCents === null) {
            throw new InvalidArgumentException('Either percentOff or amountOffCents is required.');
        }

        if ($percentOff !== null && $amountOffCents !== null) {
            throw new InvalidArgumentException('Specify percentOff OR amountOffCents, not both.');
        }

        if ($duration === 'repeating' && $durationInMonths === null) {
            throw new InvalidArgumentException('durationInMonths is required when duration is "repeating".');
        }

        $couponPayload = array_filter([
            'percent_off' => $percentOff,
            'amount_off' => $amountOffCents,
            'currency' => $amountOffCents !== null ? config('cashier.currency', 'usd') : null,
            'duration' => $duration,
            'duration_in_months' => $duration === 'repeating' ? $durationInMonths : null,
            'name' => $name,
            'max_redemptions' => $maxRedemptions,
            'metadata' => array_filter([
                'tenant_id' => $tenantId,
            ]),
        ], fn (mixed $value): bool => $value !== null);

        $coupon = $this->stripe->coupons->create($couponPayload);

        $promoPayload = array_filter([
            'coupon' => $coupon->id,
            'code' => $code,
            'max_redemptions' => $maxRedemptions,
            'expires_at' => $expiresInDays !== null ? (int) now()->addDays($expiresInDays)->timestamp : null,
        ], fn (mixed $value): bool => $value !== null);

        $promo = $this->stripe->promotionCodes->create($promoPayload);

        return new StripePromotionCodeResult(
            promotionCodeId: $promo->id,
            code: $promo->code,
            couponId: $coupon->id,
        );
    }
}
