<?php

namespace App\Enums;

enum SubscriptionTier: string
{
    case Starter = 'starter';
    case Growth = 'growth';
    case Pro = 'pro';

    public function level(): int
    {
        return match ($this) {
            self::Starter => 1,
            self::Growth => 2,
            self::Pro => 3,
        };
    }

    /**
     * Check if this tier meets or exceeds the required tier.
     */
    public function meetsRequirement(self $required): bool
    {
        return $this->level() >= $required->level();
    }
}
