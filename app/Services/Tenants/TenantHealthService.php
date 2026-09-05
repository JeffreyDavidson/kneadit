<?php

namespace App\Services\Tenants;

use App\DataTransferObjects\Settings\BrandingSettings;
use App\Models\Platform\Tenant;
use App\ValueObjects\TenantHealthScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/** @phpstan-type HealthData array{id: string, name: string, owner: string, email: string, plan: string, health_score: int, login_score: int, order_score: int, product_score: int, setup_score: int} */
class TenantHealthService
{
    public function __construct(
        protected TenancyManager $tenancyManager,
    ) {}

    /** @return Collection<int, HealthData> */
    public function getTenantHealthData(): Collection
    {
        $results = [];

        foreach (Tenant::query()->lazy() as $tenant) {
            try {
                $healthScore = $this->calculateHealthScore($tenant);
            } catch (\Throwable $exception) {
                Log::warning('Unable to calculate tenant health', [
                    'tenant_id' => $tenant->id,
                    'error' => $exception->getMessage(),
                ]);

                continue;
            }

            $results[] = [
                'id' => $tenant->id,
                'name' => $tenant->store_name ?? $tenant->name,
                'owner' => $tenant->name,
                'email' => $tenant->email,
                'plan' => $tenant->plan->value ?? 'trial',
                'health_score' => $healthScore->score,
                'login_score' => $healthScore->loginScore,
                'order_score' => $healthScore->orderScore,
                'product_score' => $healthScore->productScore,
                'setup_score' => $healthScore->setupScore,
            ];
        }

        return collect($results)->sortBy('health_score')->values();
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
    }

    /** @return array{average: float|int, healthy: int, at_risk: int, critical: int, total: int} */
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
        return $this->tenancyManager->withinTenant($tenant, fn () => DB::table('orders')
            ->where('created_at', '>=', now()->subDays($days))
            ->count());
    }
}
