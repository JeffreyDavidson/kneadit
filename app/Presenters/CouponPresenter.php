<?php

namespace App\Presenters;

use App\Enums\Financial\CouponType;
use App\Models\Financial\Coupon;

final class CouponPresenter
{
    public function __construct(
        public readonly Coupon $coupon,
    ) {}

    public static function for(Coupon $coupon): self
    {
        return new self($coupon);
    }

    public function formattedDiscount(): string
    {
        $value = $this->coupon->type === CouponType::Percentage
            ? ($this->coupon->percentage?->value() ?? 0.0)
            : ($this->coupon->fixed_amount?->dollars() ?? 0.0);

        return $this->coupon->type->formatDiscount($value);
    }
}
