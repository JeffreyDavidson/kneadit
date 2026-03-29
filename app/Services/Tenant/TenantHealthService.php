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
