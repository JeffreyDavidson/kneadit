<?php

namespace App\DataTransferObjects;

final readonly class CreateGiftCardData
{
    public function __construct(
        public float $initialBalance,
        public string $purchaserName,
        public string $purchaserEmail,
        public ?string $recipientName = null,
        public ?string $recipientEmail = null,
        public ?string $message = null,
        public ?string $expiresAt = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            initialBalance: (float) $data['initial_balance'],
            purchaserName: $data['purchaser_name'],
            purchaserEmail: $data['purchaser_email'],
            recipientName: $data['recipient_name'] ?? null,
            recipientEmail: $data['recipient_email'] ?? null,
            message: $data['message'] ?? null,
            expiresAt: $data['expires_at'] ?? null,
        );
    }
}
