<?php

namespace App\Services;

use App\DataTransferObjects\CouponValidationResult;
use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class CouponService
{
    /**
     * Validate a coupon code against the given subtotal.
     *
     * Uses lockForUpdate() for thread safety when checking validity.
     */
    public function validate(string $code, float $subtotal): CouponValidationResult
    {
        $coupon = Coupon::query()->where('code', Str::upper(trim($code)))->lockForUpdate()->first();

        if (! $coupon) {
            return CouponValidationResult::invalid('Coupon not found.');
        }

        if (! $coupon->isValid()) {
            return CouponValidationResult::invalid('This coupon is no longer valid.');
        }

        if ($coupon->min_order_amount && $subtotal < (float) $coupon->min_order_amount) {
            return CouponValidationResult::invalid('Minimum order of ' . Number::currency($coupon->min_order_amount) . ' required for this coupon.');
        }

        $discount = $this->calculateDiscount($coupon, $subtotal);

        return CouponValidationResult::valid($coupon, $discount);
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
