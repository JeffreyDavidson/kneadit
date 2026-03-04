<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasPlanGating
{
    /**
     * Plan hierarchy definition
     */
    private static array $planHierarchy = [
        'starter' => 1,
        'growth' => 2, 
        'pro' => 3,
    ];

    /**
     * Feature access by plan
     */
    private static array $planFeatures = [
        'starter' => [
            'orders',
            'products',
            'categories',
            'customers',
            'dashboard',
            'quick-order',
            'settings',
        ],
        'growth' => [
            'orders',
            'products',
            'categories',
            'customers',
            'dashboard',
            'quick-order',
            'settings',
            'expenses',
            'incomes',
            'finance-summary',
            'recipes',
            'coupons',
            'customer-notes',
            'customer-favorites',
        ],
        'pro' => [
            'orders',
            'products',
            'categories',
            'customers',
            'dashboard',
            'quick-order',
            'settings',
            'expenses',
            'incomes',
            'finance-summary',
            'recipes',
            'coupons',
            'customer-notes',
            'customer-favorites',
            'review-analytics',
            'instagram-captions',
            'holiday-planning',
            'delivery-route-planner',
            'custom-branding',
        ],
    ];

    /**
     * Check if current tenant has access to a feature
     */
    public static function hasFeatureAccess(string $feature): bool
    {
        $currentPlan = self::getCurrentPlan();
        
        // If no plan is set, default to starter
        if (!$currentPlan || !isset(self::$planFeatures[$currentPlan])) {
            $currentPlan = 'starter';
        }
        
        return in_array($feature, self::$planFeatures[$currentPlan]);
    }

    /**
     * Check if current plan has minimum required plan level
     */
    public static function hasMinimumPlan(string $requiredPlan): bool
    {
        $currentPlan = self::getCurrentPlan();
        
        if (!$currentPlan || !isset(self::$planHierarchy[$currentPlan])) {
            $currentPlan = 'starter';
        }
        
        if (!isset(self::$planHierarchy[$requiredPlan])) {
            return false;
        }
        
        return self::$planHierarchy[$currentPlan] >= self::$planHierarchy[$requiredPlan];
    }

    /**
     * Get current tenant's plan
     */
    private static function getCurrentPlan(): ?string
    {
        if (function_exists('tenant') && tenant()) {
            return tenant()->plan ?? 'starter';
        }
        
        // Fallback for non-tenant context
        return 'starter';
    }

    /**
     * Get upgrade message for restricted features
     */
    public static function getUpgradeMessage(string $requiredPlan): string
    {
        $planNames = [
            'starter' => 'Starter',
            'growth' => 'Growth',
            'pro' => 'Pro',
        ];
        
        $requiredPlanName = $planNames[$requiredPlan] ?? ucfirst($requiredPlan);
        
        return "This feature requires the {$requiredPlanName} plan or higher. Please upgrade your subscription to access this feature.";
    }

    /**
     * Check access and redirect/show message if needed (for use in resources)
     */
    public static function checkAccess(string $feature): bool
    {
        return self::hasFeatureAccess($feature);
    }

    /**
     * Check minimum plan access (for use in resources with $requiredPlan property)
     */
    public static function checkMinimumPlan(string $requiredPlan): bool
    {
        return self::hasMinimumPlan($requiredPlan);
    }
}