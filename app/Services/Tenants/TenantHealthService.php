<?php

namespace App\Services\Tenants;

use App\DataTransferObjects\Platform\TenantHealthMetrics;
use App\DataTransferObjects\Settings\BrandingSettings;
use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantInsightsMetricsQuery;
use App\ValueObjects\TenantHealthScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

/** @phpstan-type HealthRow array{id: string, name: string, owner: string, email: string, plan: string, health_score: int, login_score: int, order_score: int, product_score: int, setup_score: int} */
class TenantHealthService
{
    public function __construct(
        private readonly TenantInsightsMetricsQuery $metricsQuery,
    ) {}

    /** @return Collection<int, HealthRow> */
    public function getTenantHealthData(): Collection
    {
        return $this->getTenantHealthSnapshot()['tenants'];
    }

    /**
     * @return array{
     *     tenants: Collection<int, HealthRow>,
     *     stats: array{average: float|int, healthy: int, at_risk: int, critical: int, total: int}
     * }
     */
    public function getTenantHealthSnapshot(): array
    {
        $data = $this->metricsQuery->health()
            ->map(fn (TenantHealthMetrics $metrics): array => $this->healthRow($metrics))
            ->sortBy('health_score')
            ->values();

        return [
            'tenants' => $data,
            'stats' => $this->summarize($data),
        ];
    }

    public function calculateHealthScore(Tenant $tenant, TenantHealthMetrics $metrics): TenantHealthScore
    {
        $daysSinceLogin = $metrics->lastUserActivityAt
            ? (int) Date::parse($metrics->lastUserActivityAt)->diffInDays(now())
            : null;

        $setupCompleted = collect([
            ! empty($tenant->store_name),
            ! empty($tenant->store_logo),
            (bool) $tenant->storefront_enabled,
            ! empty($tenant->brand_color_primary) && $tenant->brand_color_primary !== BrandingSettings::DEFAULT_BRAND_COLOR,
            $metrics->totalProducts > 0,
            $metrics->totalCategories > 0,
            $metrics->totalOrders > 0,
        ])->filter()->count();

        return new TenantHealthScore(
            daysSinceLogin: $daysSinceLogin,
            orderCount: $metrics->totalOrders,
            productCount: $metrics->totalProducts,
            setupCompleted: $setupCompleted,
        );
    }

    /** @return array{average: float|int, healthy: int, at_risk: int, critical: int, total: int} */
    public function getHealthSummaryStats(): array
    {
        return $this->getTenantHealthSnapshot()['stats'];
    }

    /** @return HealthRow */
    private function healthRow(TenantHealthMetrics $metrics): array
    {
        $tenant = $metrics->tenant;
        $healthScore = $this->calculateHealthScore($tenant, $metrics);

        return [
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

    /** @param Collection<int, HealthRow> $data
     * @return array{average: float|int, healthy: int, at_risk: int, critical: int, total: int}
     */
    private function summarize(Collection $data): array
    {
        return [
            'average' => $data->count() > 0 ? round($data->avg('health_score') ?? 0) : 0,
            'healthy' => $data->filter(fn (array $tenant): bool => $tenant['health_score'] > 70)->count(),
            'at_risk' => $data->filter(fn (array $tenant): bool => $tenant['health_score'] >= 40 && $tenant['health_score'] <= 70)->count(),
            'critical' => $data->filter(fn (array $tenant): bool => $tenant['health_score'] < 40)->count(),
            'total' => $data->count(),
        ];
    }
}
