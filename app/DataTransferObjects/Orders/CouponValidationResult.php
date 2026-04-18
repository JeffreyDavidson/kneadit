<?php

namespace App\DataTransferObjects\Orders;

use App\Models\Financial\Coupon;

final readonly class CouponValidationResult
{
    private const DEFAULT_ERROR = 'Invalid coupon';

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

    /**
     * Non-null error message for failed validation results.
     * Falls back to a generic message if no specific error was set.
     */
    public function errorMessage(): string
    {
        return $this->error ?? self::DEFAULT_ERROR;
    }
}
