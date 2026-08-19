<?php

namespace App\Services\Tenants;

use App\DataTransferObjects\Settings\BrandingSettings;
use App\DataTransferObjects\Tenants\TenantHealthData;
use App\DataTransferObjects\Tenants\TenantHealthSummary;
use App\Models\Platform\Tenant;
use App\ValueObjects\TenantHealthScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class TenantHealthService
{
    public function __construct(
        protected TenancyManager $tenancyManager,
    ) {}

    /** @return Collection<int, TenantHealthData> */
    public function getTenantHealthData(): Collection
    {
        $results = [];

        foreach (Tenant::query()->lazy() as $tenant) {
            $healthScore = $this->calculateHealthScore($tenant);

            $results[] = new TenantHealthData(
                tenantId: $tenant->id,
                name: $tenant->store_name ?? $tenant->name,
                owner: $tenant->name,
                email: $tenant->email,
                plan: $tenant->plan->value ?? 'trial',
                healthScore: $healthScore->score,
                loginScore: $healthScore->loginScore,
                orderScore: $healthScore->orderScore,
                productScore: $healthScore->productScore,
                setupScore: $healthScore->setupScore,
            );
        }

        return collect($results)->sortBy('healthScore')->values();
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
                    'days_since_login' => is_string($lastLogin) ? (int) Date::parse($lastLogin)->diffInDays(now()) : null,
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

    public function getHealthSummaryStats(): TenantHealthSummary
    {
        $data = $this->getTenantHealthData();

        return new TenantHealthSummary(
            average: $data->isNotEmpty() ? round($data->avg('healthScore') ?? 0) : 0,
            healthy: $data->filter(fn (TenantHealthData $tenant) => $tenant->healthScore > 70)->count(),
            atRisk: $data->filter(fn (TenantHealthData $tenant) => $tenant->healthScore >= 40 && $tenant->healthScore <= 70)->count(),
            critical: $data->filter(fn (TenantHealthData $tenant) => $tenant->healthScore < 40)->count(),
            total: $data->count(),
        );
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
            ! empty($tenant->brand_color_primary) && $tenant->brand_color_primary !== BrandingSettings::DEFAULT_BRAND_COLOR,
            $metrics['has_products'],
            $metrics['has_categories'],
            $metrics['has_orders'],
        ])->filter()->count();
    }

    public function getLastLogin(Tenant $tenant): ?string
    {
        try {
            $lastLogin = $this->tenancyManager->withinTenant($tenant, fn () => DB::table('users')->max('updated_at'));

            return is_string($lastLogin) ? $lastLogin : null;
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
