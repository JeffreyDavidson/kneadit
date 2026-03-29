<?php

namespace App\DataTransferObjects;

final readonly class GiftCardRedemptionResult
{
    public function __construct(
        public bool $success,
        public float $amountApplied = 0,
        public float $remainingBalance = 0,
        public ?string $error = null,
    ) {}

    public static function failed(string $error): self
    {
        return new self(success: false, error: $error);
    }

    public static function redeemed(float $amountApplied, float $remainingBalance): self
    {
        return new self(success: true, amountApplied: $amountApplied, remainingBalance: $remainingBalance);
    }
}
