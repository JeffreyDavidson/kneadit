<?php

namespace App\Queries\Platform;

use App\DataTransferObjects\Platform\TenantChurnMetrics;
use App\DataTransferObjects\Platform\TenantHealthMetrics;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Throwable;

readonly class TenantInsightsMetricsQuery
{
    public function __construct(private TenancyManager $tenancyManager) {}

    /** @return Collection<int, TenantHealthMetrics> */
    public function health(): Collection
    {
        $metrics = [];

        foreach (Tenant::query()->orderBy('id')->lazy() as $tenant) {
            try {
                $metrics[] = $this->tenancyManager->withinTenant(
                    $tenant,
                    fn (): TenantHealthMetrics => $this->readHealthMetrics($tenant),
                );
            } catch (Throwable $exception) {
                $this->logHealthFailure($tenant, $exception);
            }
        }

        return collect($metrics);
    }

    /** @return Collection<int, TenantChurnMetrics> */
    public function churn(int $noOrdersDays, int $minimumTenantAgeDays): Collection
    {
        $metrics = [];

        foreach (Tenant::query()->orderBy('id')->lazy() as $tenant) {
            $shouldReadRecentOrders = $this->isEligibleForNoOrdersCheck($tenant, $minimumTenantAgeDays);

            try {
                $metrics[] = $this->tenancyManager->withinTenant(
                    $tenant,
                    function () use ($tenant, $noOrdersDays, $shouldReadRecentOrders): TenantChurnMetrics {
                        try {
                            $healthMetrics = $this->readHealthMetrics($tenant);
                        } catch (Throwable $exception) {
                            $this->logHealthFailure($tenant, $exception);
                            $healthMetrics = null;
                        }

                        $recentOrderCount = null;

                        if ($shouldReadRecentOrders) {
                            try {
                                $recentOrderCount = Order::query()
                                    ->where('created_at', '>=', now()->subDays($noOrdersDays))
                                    ->count();
                            } catch (Throwable $exception) {
                                $this->logRecentOrderFailure($tenant, $exception);
                            }
                        }

                        return new TenantChurnMetrics(
                            tenant: $tenant,
                            healthMetrics: $healthMetrics,
                            recentOrderCount: $recentOrderCount,
                        );
                    },
                );
            } catch (Throwable $exception) {
                $this->logHealthFailure($tenant, $exception);

                if ($shouldReadRecentOrders) {
                    $this->logRecentOrderFailure($tenant, $exception);
                }

                $metrics[] = new TenantChurnMetrics(
                    tenant: $tenant,
                    healthMetrics: null,
                    recentOrderCount: null,
                );
            }
        }

        return collect($metrics);
    }

    private function readHealthMetrics(Tenant $tenant): TenantHealthMetrics
    {
        $lastUserActivityAt = User::query()->max('updated_at');

        return new TenantHealthMetrics(
            tenant: $tenant,
            lastUserActivityAt: is_string($lastUserActivityAt) ? $lastUserActivityAt : null,
            totalOrders: Order::query()->count(),
            totalProducts: Product::query()->count(),
            totalCategories: Category::query()->count(),
        );
    }

    private function isEligibleForNoOrdersCheck(Tenant $tenant, int $minimumTenantAgeDays): bool
    {
        if (! $tenant->created_at) {
            return false;
        }

        return (int) Date::parse($tenant->created_at)->diffInDays(now()) > $minimumTenantAgeDays;
    }

    private function logHealthFailure(Tenant $tenant, Throwable $exception): void
    {
        Log::warning('Unable to calculate tenant health', [
            'tenant_id' => $tenant->id,
            'error' => $exception->getMessage(),
        ]);
    }

    private function logRecentOrderFailure(Tenant $tenant, Throwable $exception): void
    {
        Log::warning('Unable to evaluate tenant order churn', [
            'tenant_id' => $tenant->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
