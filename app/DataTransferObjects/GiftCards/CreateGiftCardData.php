<?php

namespace App\DataTransferObjects\GiftCards;

use DateTimeInterface;

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
            initialBalance: self::floatValue($data['initial_balance'] ?? null, 'initial_balance'),
            purchaserName: self::stringValue($data['purchaser_name'] ?? null, 'purchaser_name'),
            purchaserEmail: self::stringValue($data['purchaser_email'] ?? null, 'purchaser_email'),
            recipientName: self::nullableStringValue($data['recipient_name'] ?? null, 'recipient_name'),
            recipientEmail: self::nullableStringValue($data['recipient_email'] ?? null, 'recipient_email'),
            message: self::nullableStringValue($data['message'] ?? null, 'message'),
            expiresAt: self::nullableStringValue($data['expires_at'] ?? null, 'expires_at'),
        );
    }

    private static function stringValue(mixed $value, string $key): string
    {
        if (! is_string($value)) {
            throw new \UnexpectedValueException("Expected {$key} to be a string.");
        }

        return $value;
    }

    private static function nullableStringValue(mixed $value, string $key): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return self::stringValue($value, $key);
    }

    private static function floatValue(mixed $value, string $key): float
    {
        if (is_float($value) || is_int($value)) {
            return $value;
        }

        if (! is_string($value) || ! is_numeric($value)) {
            throw new \UnexpectedValueException("Expected {$key} to be numeric.");
        }

        return (float) $value;
    }
}
