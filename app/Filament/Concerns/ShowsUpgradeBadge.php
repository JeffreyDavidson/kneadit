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
            $current = data_get($tenant, 'plan');

            if ($current instanceof SubscriptionTier && $current->meetsRequirement(static::requiredTier())) {
                return null;
            }

            return Str::upper(static::requiredTier()->value);
        });
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $current = data_get(static::currentTenant(), 'plan');

        if ($current instanceof SubscriptionTier && $current->meetsRequirement(static::requiredTier())) {
            return null;
        }

        return static::requiredTier() === SubscriptionTier::Growth ? 'info' : 'warning';
    }

    protected static function currentTenant(): ?Tenant
    {
        $tenant = app()->bound(Tenant::class)
            ? app(Tenant::class)
            : tenancy()->tenant;

        return $tenant instanceof Tenant ? $tenant : null;
    }
}
