<?php

namespace App\Services\Tenants;

use App\Models\Platform\Tenant;
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
            $tenantScores = $this->getTenantScores($tenant);
            $setupScore = $this->getSetupScore($tenant, $tenantScores);

            $totalScore = $tenantScores['login'] + $tenantScores['order'] + $tenantScores['product'] + $setupScore;

            $results->push([
                'id' => $tenant->id,
                'name' => $tenant->store_name ?? $tenant->name,
                'owner' => $tenant->name,
                'email' => $tenant->email,
                'plan' => $tenant->plan ?? 'free',
                'health_score' => $totalScore,
                'login_score' => $tenantScores['login'],
                'order_score' => $tenantScores['order'],
                'product_score' => $tenantScores['product'],
                'setup_score' => $setupScore,
            ]);
        });

        return $results->sortBy('health_score')->values();
    }

    /**
     * Gather all tenant-side scores in a single DB context switch.
     *
     * @return array{login: int, order: int, product: int, has_products: bool, has_categories: bool, has_orders: bool}
     */
    protected function getTenantScores(Tenant $tenant): array
    {
        try {
            return $this->tenancyManager->withinTenant($tenant, function () {
                $lastLogin = DB::table('users')->max('updated_at');
                $orderCount = DB::table('orders')
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count();
                $productCount = DB::table('products')->count();
                $categoryCount = DB::table('categories')->count();
                $hasOrders = DB::table('orders')->exists();

                return [
                    'login' => $this->scoreLogin($lastLogin),
                    'order' => $this->scoreOrder($orderCount),
                    'product' => $this->scoreProduct($productCount),
                    'has_products' => $productCount > 0,
                    'has_categories' => $categoryCount > 0,
                    'has_orders' => $hasOrders,
                ];
            });
        } catch (\Throwable) {
            return [
                'login' => 0,
                'order' => 0,
                'product' => 0,
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

    protected function scoreLogin(?string $lastLogin): int
    {
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

    protected function scoreOrder(int $count): int
    {
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

    protected function scoreProduct(int $count): int
    {
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

    /**
     * @param array{has_products: bool, has_categories: bool, has_orders: bool} $tenantScores
     */
    protected function getSetupScore(Tenant $tenant, array $tenantScores): int
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

        if ($tenantScores['has_products']) {
            $completed++;
        }
        if ($tenantScores['has_categories']) {
            $completed++;
        }
        if ($tenantScores['has_orders']) {
            $completed++;
        }

        return (int) round($completed * $pointsPer);
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
