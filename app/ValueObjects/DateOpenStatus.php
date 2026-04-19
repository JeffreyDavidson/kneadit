<?php

namespace App\ValueObjects;

final readonly class DateOpenStatus
{
    public function __construct(
        public bool $open,
        public ?string $reason,
    ) {}

    public static function open(): self
    {
        return new self(open: true, reason: null);
    }

    public static function blocked(?string $reason = null): self
    {
        return new self(open: false, reason: $reason ?? 'Blocked');
    }

    public static function closed(): self
    {
        return new self(open: false, reason: 'Closed');
    }
}
