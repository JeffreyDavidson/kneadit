<?php

namespace App\Services;

use App\Enums\CouponType;
use App\Models\Coupon;

class CouponService
{
    /**
     * Validate a coupon code against the given subtotal.
     *
     * Uses lockForUpdate() for thread safety when checking validity.
     *
     * @return array{valid: bool, coupon: Coupon|null, discount: float, error: string|null}
     */
    public function validate(string $code, float $subtotal): array
    {
        $coupon = Coupon::query()->where('code', strtoupper(trim($code)))->lockForUpdate()->first();

        if (! $coupon) {
            return ['valid' => false, 'coupon' => null, 'discount' => 0, 'error' => 'Coupon not found.'];
        }

        if (! $coupon->isValid()) {
            return ['valid' => false, 'coupon' => null, 'discount' => 0, 'error' => 'This coupon is no longer valid.'];
        }

        if ($coupon->min_order_amount && $subtotal < (float) $coupon->min_order_amount) {
            return [
                'valid' => false,
                'coupon' => null,
                'discount' => 0,
                'error' => 'Minimum order of $' . number_format($coupon->min_order_amount, 2) . ' required for this coupon.',
            ];
        }

        $discount = $this->calculateDiscount($coupon, $subtotal);

        return ['valid' => true, 'coupon' => $coupon, 'discount' => $discount, 'error' => null];
    }

    /**
     * Calculate the discount amount for a coupon against the given subtotal.
     */
    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        if ($coupon->min_order_amount && $subtotal < (float) $coupon->min_order_amount) {
            return 0;
        }

        if ($coupon->type === CouponType::Percentage) {
            return round($subtotal * ((float) $coupon->value / 100), 2);
        }

        return round(min((float) $coupon->value, $subtotal), 2);
    }

    /**
     * Increment the coupon's used_count atomically.
     */
    public function apply(Coupon $coupon): void
    {
        Coupon::query()->where('id', $coupon->id)->increment('used_count');
    }
}
