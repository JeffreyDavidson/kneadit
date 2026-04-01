<?php

namespace App\Enums\Platform;

use Filament\Support\Contracts\HasLabel;

enum SubscriptionTier: string implements HasLabel
{
    case Starter = 'starter';
    case Growth = 'growth';
    case Pro = 'pro';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

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

    public static function fromPriceId(string $priceId): ?self
    {
        $plan = array_search($priceId, config('kneadit.stripe_prices', []), true);

        return $plan ? self::tryFrom($plan) : null;
    }
}
