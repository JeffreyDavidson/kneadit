<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Stripe;

use Livewire\Wireable;
use UnexpectedValueException;

final readonly class StripePromotionCodeResult implements Wireable
{
    public function __construct(
        public string $promotionCodeId,
        public string $code,
        public string $couponId,
    ) {}

    /** @return array{promotionCodeId: string, code: string, couponId: string} */
    public function toLivewire(): array
    {
        return [
            'promotionCodeId' => $this->promotionCodeId,
            'code' => $this->code,
            'couponId' => $this->couponId,
        ];
    }

    public static function fromLivewire(mixed $value): self
    {
        if (
            ! is_array($value)
            || ! is_string($value['promotionCodeId'] ?? null)
            || ! is_string($value['code'] ?? null)
            || ! is_string($value['couponId'] ?? null)
        ) {
            throw new UnexpectedValueException('Expected the promotion code result payload to hold string ids and code.');
        }

        return new self(
            promotionCodeId: $value['promotionCodeId'],
            code: $value['code'],
            couponId: $value['couponId'],
        );
    }
}
