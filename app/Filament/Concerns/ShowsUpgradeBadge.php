<?php

namespace App\Filament\Concerns;

use App\Enums\Platform\SubscriptionTier;
use Illuminate\Support\Str;

trait ShowsUpgradeBadge
{
    abstract protected static function requiredTier(): SubscriptionTier;

    public static function getNavigationBadge(): ?string
    {
        return cache()->remember('navigation-badge:upgrade:' . static::class . ':' . (tenant()?->getTenantKey() ?? 'central') . ':' . static::requiredTier()->value, 60, function (): ?string {
            $current = tenant()?->plan;

            if ($current?->meetsRequirement(static::requiredTier()) ?? false) {
                return null;
            }

            return Str::upper(static::requiredTier()->value);
        });
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $current = tenant()?->plan;

        if ($current?->meetsRequirement(static::requiredTier()) ?? false) {
            return null;
        }

        return static::requiredTier() === SubscriptionTier::Growth ? 'info' : 'warning';
    }
}
