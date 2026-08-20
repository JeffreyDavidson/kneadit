<?php

namespace App\Filament\Concerns;

use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use Illuminate\Support\Str;

trait ShowsUpgradeBadge
{
    abstract protected static function requiredTier(): SubscriptionTier;

    public static function getNavigationBadge(): ?string
    {
        $tenant = static::currentTenant();
        $tenantKey = $tenant instanceof Tenant ? $tenant->id : 'central';

        return cache()->remember('navigation-badge:upgrade:' . static::class . ':' . $tenantKey . ':' . static::requiredTier()->value, 60, function () use ($tenant): ?string {
            $current = $tenant?->plan;

            if ($current?->meetsRequirement(static::requiredTier()) ?? false) {
                return null;
            }

            return Str::upper(static::requiredTier()->value);
        });
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $current = static::currentTenant()?->plan;

        if ($current?->meetsRequirement(static::requiredTier()) ?? false) {
            return null;
        }

        return static::requiredTier() === SubscriptionTier::Growth ? 'info' : 'warning';
    }

    protected static function currentTenant(): ?Tenant
    {
        $tenant = tenancy()->tenant;

        return $tenant instanceof Tenant ? $tenant : null;
    }
}
