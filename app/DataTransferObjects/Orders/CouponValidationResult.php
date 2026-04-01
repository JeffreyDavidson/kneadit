<?php

namespace App\DataTransferObjects\Orders;

use App\Models\Financial\Coupon;

final readonly class CouponValidationResult
{
    public function __construct(
        public bool $valid,
        public ?Coupon $coupon = null,
        public float $discount = 0,
        public ?string $error = null,
    ) {}

    public static function invalid(string $error): self
    {
        return new self(valid: false, error: $error);
    }

    public static function valid(Coupon $coupon, float $discount): self
    {
        return new self(valid: true, coupon: $coupon, discount: $discount);
    }
}
