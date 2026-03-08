<?php

namespace App\Traits;

trait HasPlanGating
{
    /**
     * Plan hierarchy - higher number = more access.
     */
    private static array $planHierarchy = [
        'starter' => 1,
        'growth' => 2,
        'pro' => 3,
    ];

    /**
     * Override in each resource/page to set the minimum plan required.
     */
    public static function getRequiredPlan(): string
    {
        return property_exists(static::class, 'requiredPlan')
            ? static::$requiredPlan
            : 'starter';
    }

    /**
     * Get current tenant's plan.
     */
    private static function getCurrentPlan(): string
    {
        if (function_exists('tenant') && tenant()) {
            return tenant()->plan ?? 'starter';
        }

        return 'starter';
    }

    /**
     * Check if current tenant meets the minimum plan requirement.
     */
    public static function hasMinimumPlan(string $requiredPlan): bool
    {
        $currentPlan = static::getCurrentPlan();

        return (self::$planHierarchy[$currentPlan] ?? 1) >= (self::$planHierarchy[$requiredPlan] ?? 1);
    }

    /**
     * Filament calls this to determine if the resource/page is accessible.
     * We always return true so the nav item shows, but mount() will redirect.
     */
    public static function canAccess(): bool
    {
        return static::hasMinimumPlan(static::getRequiredPlan());
    }

    /**
     * Show a plan badge on nav items the baker can't access yet.
     */
    public static function getNavigationBadge(): ?string
    {
        if (! static::hasMinimumPlan(static::getRequiredPlan())) {
            return strtoupper(static::getRequiredPlan());
        }

        return null;
    }

    /**
     * Badge color for locked features.
     */
    public static function getNavigationBadgeColor(): ?string
    {
        if (! static::hasMinimumPlan(static::getRequiredPlan())) {
            return static::getRequiredPlan() === 'pro' ? 'warning' : 'info';
        }

        return null;
    }

    /**
     * Keep nav items visible even when plan-gated.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    /**
     * Get upgrade message for the required plan.
     */
    public static function getUpgradeMessage(): string
    {
        $planNames = ['starter' => 'Starter', 'growth' => 'Growth', 'pro' => 'Pro'];
        $name = $planNames[static::getRequiredPlan()] ?? ucfirst(static::getRequiredPlan());

        return "This feature requires the {$name} plan or higher. Please upgrade to access it.";
    }

    // Keep backward compat
    public static function checkMinimumPlan(string $requiredPlan): bool
    {
        return static::hasMinimumPlan($requiredPlan);
    }

    public static function hasFeatureAccess(string $feature): bool
    {
        return true; // Deprecated - use plan hierarchy instead
    }
}
