<?php

namespace App\Traits;

use App\Enums\SubscriptionTier;
use Illuminate\Support\Facades\Auth;

trait HasPlanGating
{
    public static function getRequiredPlan(): string
    {
        return property_exists(static::class, 'requiredPlan')
            ? static::$requiredPlan
            : 'starter';
    }

    public static function canAccess(): bool
    {
        // Check role access if RequiresRole trait methods are available
        if (method_exists(static::class, 'getRequiredRole')) {
            $user = Auth::user();
            if (! $user || ! $user->hasMinRole(static::getRequiredRole())) {
                return false;
            }
        }

        // Check plan access
        $current = SubscriptionTier::tryFrom(tenant()?->plan ?? 'starter');
        $required = SubscriptionTier::tryFrom(static::getRequiredPlan());

        if (! $current || ! $required) {
            return false;
        }

        return $current->meetsRequirement($required);
    }

    public static function getNavigationBadge(): ?string
    {
        $current = SubscriptionTier::tryFrom(tenant()?->plan ?? 'starter');
        $required = SubscriptionTier::tryFrom(static::getRequiredPlan());

        if (! $current || ! $required || $current->meetsRequirement($required)) {
            return null;
        }

        return strtoupper($required->value);
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $current = SubscriptionTier::tryFrom(tenant()?->plan ?? 'starter');
        $required = SubscriptionTier::tryFrom(static::getRequiredPlan());

        if (! $current || ! $required || $current->meetsRequirement($required)) {
            return null;
        }

        return $required === SubscriptionTier::Growth ? 'info' : 'warning';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
