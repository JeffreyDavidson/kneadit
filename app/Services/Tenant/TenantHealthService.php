<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class TenantHealthService
{
    public function __construct(
        protected TenancyManager $tenancyManager,
    ) {}

    /** @return Collection<int, mixed> */
    public function getTenantHealthData(): Collection
    {
        return Tenant::all()->map(function (Tenant $tenant) {
            $loginScore = $this->getLoginScore($tenant);
            $orderScore = $this->getOrderScore($tenant);
            $productScore = $this->getProductScore($tenant);
            $setupScore = $this->getSetupScore($tenant);

            return [
                'id' => $tenant->id,
                'name' => $tenant->store_name ?? $tenant->name,
                'owner' => $tenant->name,
                'email' => $tenant->email,
                'plan' => $tenant->plan ?? 'free',
                'health_score' => $loginScore + $orderScore + $productScore + $setupScore,
                'login_score' => $loginScore,
                'order_score' => $orderScore,
                'product_score' => $productScore,
                'setup_score' => $setupScore,
            ];
        })->sortBy('health_score')->values();
    }

    /** @return array<string, mixed> */
    public function getHealthSummaryStats(): array
    {
        $data = $this->getTenantHealthData();

        return [
            'average' => $data->count() > 0 ? round($data->avg('health_score') ?? 0) : 0,
            'healthy' => $data->filter(fn (array $t) => $t['health_score'] > 70)->count(),
            'at_risk' => $data->filter(fn (array $t) => $t['health_score'] >= 40 && $t['health_score'] <= 70)->count(),
            'critical' => $data->filter(fn (array $t) => $t['health_score'] < 40)->count(),
            'total' => $data->count(),
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    public function getAlerts(): Collection
    {
        $tenants = Tenant::all();
        $healthData = $this->getTenantHealthData()->keyBy('id');
        $alerts = collect();

        foreach ($tenants as $tenant) {
            $daysSinceSignup = $tenant->created_at ? (int) Date::parse($tenant->created_at)->diffInDays(now()) : 0;
            $health = $healthData->get($tenant->id);
            $healthScore = $health ? $health['health_score'] : 0;

            if ($tenant->trial_ends_at) {
                $trialEnds = Date::parse($tenant->trial_ends_at);
                if ($trialEnds->isFuture() && $trialEnds->diffInHours(now()) <= config('monitoring.churn_trial_alert_hours', 48)) {
                    $setupScore = $health ? $health['setup_score'] : 0;
                    if ($setupScore < config('monitoring.churn_low_setup_threshold', 15)) {
                        $alerts->push([
                            'tenant_id' => $tenant->id,
                            'name' => $tenant->store_name ?? $tenant->name,
                            'type' => 'trial_expiring',
                            'type_label' => 'Trial Expiring',
                            'description' => "Trial ends {$trialEnds->diffForHumans()} with less than 50% setup complete.",
                            'days_since_signup' => $daysSinceSignup,
                            'severity' => 'critical',
                        ]);
                    }
                }
            }

            $lastLogin = $this->getLastLogin($tenant);
            if ($lastLogin && Date::parse($lastLogin)->diffInDays(now()) >= config('monitoring.churn_no_login_days', 7)) {
                $days = (int) Date::parse($lastLogin)->diffInDays(now());
                $alerts->push([
                    'tenant_id' => $tenant->id,
                    'name' => $tenant->store_name ?? $tenant->name,
                    'type' => 'no_login',
                    'type_label' => 'No Recent Login',
                    'description' => "No login activity in {$days} days.",
                    'days_since_signup' => $daysSinceSignup,
                    'severity' => 'warning',
                ]);
            }

            if ($daysSinceSignup > config('monitoring.churn_min_tenant_age_days', 14)) {
                $recentOrders = $this->getRecentOrderCount($tenant, config('monitoring.churn_no_orders_days', 30));
                if ($recentOrders === 0) {
                    $alerts->push([
                        'tenant_id' => $tenant->id,
                        'name' => $tenant->store_name ?? $tenant->name,
                        'type' => 'no_orders',
                        'type_label' => 'No Orders',
                        'description' => 'Zero orders in the last ' . config('monitoring.churn_no_orders_days', 30) . ' days.',
                        'days_since_signup' => $daysSinceSignup,
                        'severity' => 'warning',
                    ]);
                }
            }

            if ($healthScore < 40) {
                $alerts->push([
                    'tenant_id' => $tenant->id,
                    'name' => $tenant->store_name ?? $tenant->name,
                    'type' => 'low_health',
                    'type_label' => 'Critical Health',
                    'description' => "Health score is {$healthScore}/100.",
                    'days_since_signup' => $daysSinceSignup,
                    'severity' => 'critical',
                ]);
            }
        }

        return $alerts->sortByDesc(fn (array $a) => $a['severity'] === 'critical' ? 1 : 0)->values();
    }

    public const PLAN_LIMITS = [
        'starter' => ['products' => 15, 'orders_per_month' => 50, 'label' => 'Starter'],
        'growth' => ['products' => 50, 'orders_per_month' => 200, 'label' => 'Growth'],
        'pro' => ['products' => null, 'orders_per_month' => null, 'label' => 'Pro'],
    ];

    /** @return Collection<int, array<string, mixed>> */
    public function getTenantUsageData(): Collection
    {
        $results = collect();

        foreach (Tenant::all() as $tenant) {
            $plan = strtolower($tenant->plan ?? 'starter');
            $limits = self::PLAN_LIMITS[$plan] ?? self::PLAN_LIMITS['starter'];

            if ($plan === 'pro') {
                continue;
            }

            try {
                [$productCount, $orderCount] = $this->tenancyManager->withinTenant($tenant, function () {
                    return [
                        DB::table('products')->count(),
                        DB::table('orders')
                            ->whereMonth('created_at', Date::now()->month)
                            ->whereYear('created_at', Date::now()->year)
                            ->count(),
                    ];
                });

                $productLimit = $limits['products'];
                $orderLimit = $limits['orders_per_month'];
                $productPercent = $productLimit ? round(($productCount / $productLimit) * 100) : 0;
                $orderPercent = $orderLimit ? round(($orderCount / $orderLimit) * 100) : 0;

                if ($productPercent >= 80 || $orderPercent >= 80) {
                    $results->push([
                        'tenant' => $tenant,
                        'name' => $tenant->store_name ?? $tenant->name ?? $tenant->id,
                        'plan' => $limits['label'],
                        'plan_key' => $plan,
                        'product_count' => $productCount,
                        'product_limit' => $productLimit,
                        'product_percent' => min($productPercent, 100),
                        'order_count' => $orderCount,
                        'order_limit' => $orderLimit,
                        'order_percent' => min($orderPercent, 100),
                        'at_limit' => $productPercent >= 100 || $orderPercent >= 100,
                        'approaching_limit' => ! ($productPercent >= 100 || $orderPercent >= 100),
                    ]);
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $results->sortByDesc(fn (array $t) => max($t['product_percent'], $t['order_percent']));
    }

    public function getNextPlan(string $currentPlan): ?string
    {
        return match ($currentPlan) {
            'starter' => 'Growth',
            'growth' => 'Pro',
            default => null,
        };
    }

    protected function getLoginScore(Tenant $tenant): int
    {
        try {
            $lastLogin = $this->tenancyManager->withinTenant($tenant, fn () => DB::table('users')->max('updated_at'));
        } catch (\Throwable) {
            return 0;
        }

        if (! $lastLogin) {
            return 0;
        }

        $days = Date::parse($lastLogin)->diffInDays(now());

        if ($days <= 7) {
            return 25;
        }
        if ($days <= 14) {
            return 15;
        }
        if ($days <= 30) {
            return 5;
        }

        return 0;
    }

    protected function getOrderScore(Tenant $tenant): int
    {
        try {
            $count = $this->tenancyManager->withinTenant($tenant, fn () => DB::table('orders')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count());
        } catch (\Throwable) {
            return 0;
        }

        if ($count >= 10) {
            return 25;
        }
        if ($count >= 5) {
            return 15;
        }
        if ($count >= 1) {
            return 10;
        }

        return 0;
    }

    protected function getProductScore(Tenant $tenant): int
    {
        try {
            $count = $this->tenancyManager->withinTenant($tenant, fn () => DB::table('products')->count());
        } catch (\Throwable) {
            return 0;
        }

        if ($count >= 10) {
            return 20;
        }
        if ($count >= 5) {
            return 15;
        }
        if ($count >= 1) {
            return 5;
        }

        return 0;
    }

    protected function getSetupScore(Tenant $tenant): int
    {
        $pointsPer = 30 / 7;
        $completed = 0;

        if (! empty($tenant->store_name)) {
            $completed++;
        }
        if (! empty($tenant->store_logo)) {
            $completed++;
        }
        if ($tenant->storefront_enabled) {
            $completed++;
        }
        if (! empty($tenant->brand_color_primary) && $tenant->brand_color_primary !== '#d4920c') {
            $completed++;
        }

        try {
            $completed += $this->tenancyManager->withinTenant($tenant, function () {
                $extra = 0;
                if (DB::table('products')->count() > 0) {
                    $extra++;
                }
                if (DB::table('categories')->count() > 0) {
                    $extra++;
                }
                if (DB::table('orders')->count() > 0) {
                    $extra++;
                }

                return $extra;
            });
        } catch (\Throwable) {
            // Tenant DB may not exist yet
        }

        return (int) round($completed * $pointsPer);
    }

    protected function getLastLogin(Tenant $tenant): ?string
    {
        try {
            return $this->tenancyManager->withinTenant($tenant, fn () => DB::table('users')->max('updated_at'));
        } catch (\Throwable) {
            return null;
        }
    }

    protected function getRecentOrderCount(Tenant $tenant, int $days): int
    {
        try {
            return $this->tenancyManager->withinTenant($tenant, fn () => DB::table('orders')
                ->where('created_at', '>=', now()->subDays($days))
                ->count());
        } catch (\Throwable) {
            return 0;
        }
    }
}
