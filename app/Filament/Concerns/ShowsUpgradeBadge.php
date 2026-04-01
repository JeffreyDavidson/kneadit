<?php

namespace App\Filament\Concerns;

use App\Enums\Platform\SubscriptionTier;
use Illuminate\Support\Str;

trait ShowsUpgradeBadge
{
    abstract protected static function requiredTier(): SubscriptionTier;

    public static function getNavigationBadge(): ?string
    {
        $current = SubscriptionTier::tryFrom(tenant()?->plan);

        if ($current?->meetsRequirement(static::requiredTier()) ?? false) {
            return null;
        }

        return Str::upper(static::requiredTier()->value);
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $current = SubscriptionTier::tryFrom(tenant()?->plan);

        if ($current?->meetsRequirement(static::requiredTier()) ?? false) {
            return null;
        }

        return static::requiredTier() === SubscriptionTier::Growth ? 'info' : 'warning';
    }
}
