<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasPlanGating
{
    private static array $planLevels = ['starter' => 1, 'growth' => 2, 'pro' => 3];

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
            if (!$user || !$user->hasMinRole(static::getRequiredRole())) {
                return false;
            }
        }

        // Check plan access
        $current = tenant()?->plan ?? 'starter';
        $required = static::getRequiredPlan();

        return (self::$planLevels[$current] ?? 1) >= (self::$planLevels[$required] ?? 1);
    }

    public static function getNavigationBadge(): ?string
    {
        $current = tenant()?->plan ?? 'starter';
        $required = static::getRequiredPlan();

        if ((self::$planLevels[$current] ?? 1) >= (self::$planLevels[$required] ?? 1)) {
            return null;
        }

        return strtoupper($required);
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $current = tenant()?->plan ?? 'starter';
        $required = static::getRequiredPlan();

        if ((self::$planLevels[$current] ?? 1) >= (self::$planLevels[$required] ?? 1)) {
            return null;
        }

        return $required === 'growth' ? 'info' : 'warning';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
