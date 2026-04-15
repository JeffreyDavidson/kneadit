<?php

namespace App\Services\Tenants;

use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\TenantHealthScore;
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
        $results = collect();

        Tenant::query()->lazy()->each(function (Tenant $tenant) use ($results) {
            $healthScore = $this->calculateHealthScore($tenant);

            $results->push([
                'id' => $tenant->id,
                'name' => $tenant->store_name ?? $tenant->name,
                'owner' => $tenant->name,
                'email' => $tenant->email,
                'plan' => $tenant->plan?->value ?? 'trial',
                'health_score' => $healthScore->score,
                'login_score' => $healthScore->loginScore,
                'order_score' => $healthScore->orderScore,
                'product_score' => $healthScore->productScore,
                'setup_score' => $healthScore->setupScore,
            ]);
        });

        return $results->sortBy('health_score')->values();
    }

    public function calculateHealthScore(Tenant $tenant): TenantHealthScore
    {
        $metrics = $this->getTenantMetrics($tenant);
        $setupCompleted = $this->countSetupCompleted($tenant, $metrics);

        return new TenantHealthScore(
            daysSinceLogin: $metrics['days_since_login'],
            orderCount: $metrics['total_orders'],
            productCount: $metrics['total_products'],
            setupCompleted: $setupCompleted,
        );
    }

    /**
     * @return array{days_since_login: ?int, total_orders: int, total_products: int, has_products: bool, has_categories: bool, has_orders: bool}
     */
    protected function getTenantMetrics(Tenant $tenant): array
    {
        try {
            return $this->tenancyManager->withinTenant($tenant, function () {
                $lastLogin = DB::table('users')->max('updated_at');
                $orderCount = DB::table('orders')->count();
                $productCount = DB::table('products')->count();
                $categoryCount = DB::table('categories')->count();

                return [
                    'days_since_login' => $lastLogin ? (int) Date::parse($lastLogin)->diffInDays(now()) : null,
                    'total_orders' => $orderCount,
                    'total_products' => $productCount,
                    'has_products' => $productCount > 0,
                    'has_categories' => $categoryCount > 0,
                    'has_orders' => $orderCount > 0,
                ];
            });
        } catch (\Throwable) {
            return [
                'days_since_login' => null,
                'total_orders' => 0,
                'total_products' => 0,
                'has_products' => false,
                'has_categories' => false,
                'has_orders' => false,
            ];
        }
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

    /**
     * @param array{has_products: bool, has_categories: bool, has_orders: bool} $metrics
     */
    protected function countSetupCompleted(Tenant $tenant, array $metrics): int
    {
        return collect([
            ! empty($tenant->store_name),
            ! empty($tenant->store_logo),
            (bool) $tenant->storefront_enabled,
            ! empty($tenant->brand_color_primary) && $tenant->brand_color_primary !== TenantSettings::DEFAULT_BRAND_COLOR,
            $metrics['has_products'],
            $metrics['has_categories'],
            $metrics['has_orders'],
        ])->filter()->count();
    }

    public function getLastLogin(Tenant $tenant): ?string
    {
        try {
            return $this->tenancyManager->withinTenant($tenant, fn () => DB::table('users')->max('updated_at'));
        } catch (\Throwable) {
            return null;
        }
    }

    public function getRecentOrderCount(Tenant $tenant, int $days): int
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
