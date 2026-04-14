<?php

namespace App\Services\Coupon;

use App\DataTransferObjects\Orders\CouponValidationResult;
use App\Enums\Financial\CouponType;
use App\Models\Financial\Coupon;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class CouponService
{
    public function isValid(Coupon $coupon): bool
    {
        return $coupon->isValid();
    }

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

        if (! $this->isValid($coupon)) {
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
