<?php

namespace App\Services\Coupon;

use App\DataTransferObjects\Orders\CouponValidationResult;
use App\Enums\Financial\CouponType;
use App\Models\Financial\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CouponService
{
    public function isValid(Coupon $coupon): bool
    {
        if (! $coupon->is_active) {
            return false;
        }

        if ($coupon->starts_at?->isFuture()) {
            return false;
        }

        if ($coupon->expires_at?->isPast()) {
            return false;
        }

        if ($coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses) {
            return false;
        }

        return true;
    }

    public function validate(string $code, float $subtotal): CouponValidationResult
    {
        return DB::transaction(function () use ($code, $subtotal) {
            $coupon = Coupon::query()->where('code', Str::upper(trim($code)))->lockForUpdate()->first();

            if (! $coupon) {
                return CouponValidationResult::invalid('Coupon not found.');
            }

            if (! $this->isValid($coupon)) {
                return CouponValidationResult::invalid('This coupon is no longer valid.');
            }

            if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount->dollars()) {
                return CouponValidationResult::invalid('Minimum order of ' . $coupon->min_order_amount->formatted() . ' required for this coupon.');
            }

            $discount = $this->calculateDiscount($coupon, $subtotal);

            return CouponValidationResult::valid($coupon, $discount);
        });
    }

    /**
     * Calculate the discount amount for a coupon against the given subtotal.
     */
    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount->dollars()) {
            return 0;
        }

        if ($coupon->type === CouponType::Percentage) {
            return round($coupon->percentage?->of($subtotal) ?? 0, 2);
        }

        return round(min($coupon->fixed_amount?->dollars() ?? 0, $subtotal), 2);
    }
}
