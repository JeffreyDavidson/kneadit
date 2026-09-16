<?php

namespace App\Queries\Platform;

use App\DataTransferObjects\Platform\TenantComparisonResult;
use App\DataTransferObjects\Platform\TenantLeaderboardEntry;
use App\DataTransferObjects\Platform\TenantLeaderboardSummary;
use App\Models\Platform\Tenant;
use App\ValueObjects\TenantHealthScore;
use Illuminate\Support\Facades\Date;

class TenantComparisonQuery
{
    public function __construct(
        private readonly TenantComparisonMetricsQuery $metricsQuery,
    ) {}

    /** @return array<string, string> */
    public function allTenants(): array
    {
        return Tenant::query()->orderBy('store_name')
            ->get()
            ->mapWithKeys(fn (Tenant $t) => [$t->id => $t->store_name ?: $t->name])
            ->all();
    }

    /**
     * @param array<int, string> $tenantIds
     * @return array<int, array<string, mixed>>
     */
    /**
     * @param list<string> $tenantIds
     * @return list<TenantComparisonResult>
     */
    public function comparison(array $tenantIds): array
    {
        if (empty($tenantIds)) {
            return [];
        }

        $tenants = Tenant::query()->whereIn('id', $tenantIds)->get();
        $results = [];

        /** @var Tenant $tenant */
        foreach ($tenants as $tenant) {
            $metrics = $this->metricsQuery->forTenant($tenant);

            $setupChecks = [
                ! empty($tenant->store_name),
                ! empty($tenant->store_logo),
                (bool) $tenant->storefront_enabled,
                ! empty($tenant->brand_color_primary) && $tenant->brand_color_primary !== '#d4920c',
                $metrics->totalProducts > 0,
                $metrics->totalCategories > 0,
                $metrics->totalOrders > 0,
            ];

            $data = new TenantComparisonResult(
                id: $metrics->id,
                name: $metrics->name,
                plan: $metrics->plan,
                totalOrders: $metrics->totalOrders,
                monthOrders: $metrics->monthOrders,
                totalProducts: $metrics->totalProducts,
                totalCategories: $metrics->totalCategories,
                avgReview: $metrics->avgReview,
                daysSinceSignup: $tenant->created_at ? (int) Date::parse($tenant->created_at)->diffInDays(now()) : 0,
                setupCompleted: collect($setupChecks)->filter()->count(),
                healthScore: self::calculateHealthScore($tenant, [
                    'total_orders' => $metrics->totalOrders,
                    'total_products' => $metrics->totalProducts,
                    'setup_completed' => collect($setupChecks)->filter()->count(),
                ]),
            );

            $results[] = $data;
        }

        return $results;
    }

    /**
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     plan: string,
     *     total_orders: int,
     *     month_orders: int,
     *     total_products: int,
     *     total_categories: int,
     *     avg_review: float,
     *     owner: string,
     *     email: string
     * }>
     */
    /** @return list<TenantLeaderboardEntry> */
    public function leaderboard(): array
    {
        $tenants = Tenant::all();
        $results = [];

        /** @var Tenant $tenant */
        foreach ($tenants as $tenant) {
            $metrics = $this->metricsQuery->forTenant($tenant);

            $results[] = new TenantLeaderboardEntry(
                id: $metrics->id,
                name: $metrics->name,
                plan: $metrics->plan,
                totalOrders: $metrics->totalOrders,
                monthOrders: $metrics->monthOrders,
                totalProducts: $metrics->totalProducts,
                totalCategories: $metrics->totalCategories,
                avgReview: $metrics->avgReview,
                owner: $tenant->name,
                email: $tenant->email,
            );
        }

        usort($results, fn (TenantLeaderboardEntry $a, TenantLeaderboardEntry $b) => $b->totalOrders <=> $a->totalOrders);

        return $results;
    }

    public function leaderboardSummary(): TenantLeaderboardSummary
    {
        $data = self::leaderboard();
        $totalOrders = array_sum(array_map(static fn (TenantLeaderboardEntry $entry): int => $entry->totalOrders, $data));
        $totalBakeries = count($data);
        $activeOrderCounts = array_filter($data, static fn (TenantLeaderboardEntry $entry): bool => $entry->totalOrders > 0);
        $activeBakeries = count($activeOrderCounts);

        return new TenantLeaderboardSummary(
            totalOrders: $totalOrders,
            totalBakeries: $totalBakeries,
            activeBakeries: $activeBakeries,
            averageOrdersActive: $activeBakeries > 0
                ? round($totalOrders / $activeBakeries, 1)
                : 0,
        );
    }

    /** @param array{total_orders: int, total_products: int, setup_completed: int} $data */
    public static function calculateHealthScore(Tenant $tenant, array $data): int
    {
        $daysSinceLogin = $tenant->last_login_at
            ? (int) Date::parse($tenant->last_login_at)->diffInDays(now())
            : null;

        $healthScore = new TenantHealthScore(
            daysSinceLogin: $daysSinceLogin,
            orderCount: $data['total_orders'],
            productCount: $data['total_products'],
            setupCompleted: $data['setup_completed'],
        );

        return $healthScore->score;
    }
}
