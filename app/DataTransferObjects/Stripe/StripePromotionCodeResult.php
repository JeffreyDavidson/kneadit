<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Stripe;

final readonly class StripePromotionCodeResult
{
    public function __construct(
        public string $promotionCodeId,
        public string $code,
        public string $couponId,
    ) {}
}
