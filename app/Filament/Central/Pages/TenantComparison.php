<?php

namespace App\Filament\Central\Pages;

use App\DataTransferObjects\Platform\TenantMetrics;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use App\ValueObjects\TenantHealthScore;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class TenantComparison extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Bakery Comparison';

    protected string $view = 'filament.central.pages.tenant-comparison';

    public string $activeTab = 'compare';

    /** @var array<string, mixed> */
    public array $selectedTenants = [];

    public function mount(): void
    {
        $ids = request()->query('tenants', []);
        if (is_array($ids)) {
            $this->selectedTenants = array_slice(array_filter($ids), 0, 3);
        }
    }

    /** @return array<string, mixed> */
    public function getAllTenants(): array
    {
        return Tenant::query()->orderBy('store_name')
            ->get()
            ->mapWithKeys(fn (Tenant $t) => [$t->id => $t->store_name ?: $t->name])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function getComparisonData(): array
    {
        if (empty($this->selectedTenants)) {
            return [];
        }

        $tenants = Tenant::query()->whereIn('id', $this->selectedTenants)->get();
        $results = [];

        /** @var Tenant $tenant */
        foreach ($tenants as $tenant) {
            $metrics = $this->collectTenantMetrics($tenant);

            $setupChecks = [
                ! empty($tenant->store_name),
                ! empty($tenant->store_logo),
                (bool) $tenant->storefront_enabled,
                ! empty($tenant->brand_color_primary) && $tenant->brand_color_primary !== '#d4920c',
                $metrics->totalProducts > 0,
                $metrics->totalCategories > 0,
                $metrics->totalOrders > 0,
            ];

            $data = [
                'id' => $metrics->id,
                'name' => $metrics->name,
                'plan' => $metrics->plan,
                'total_orders' => $metrics->totalOrders,
                'month_orders' => $metrics->monthOrders,
                'total_products' => $metrics->totalProducts,
                'total_categories' => $metrics->totalCategories,
                'avg_review' => $metrics->avgReview,
                'days_since_signup' => $tenant->created_at ? (int) Date::parse($tenant->created_at)->diffInDays(now()) : 0,
                'setup_completed' => collect($setupChecks)->filter()->count(),
            ];

            $data['health_score'] = $this->calculateHealthScore($tenant, $data);

            $results[] = $data;
        }

        return $results;
    }

    protected function calculateHealthScore(Tenant $tenant, array $data): int
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

    // ── Revenue Leaderboard Methods ──

    /** @return array<int, array<string, mixed>> */
    public function getLeaderboardData(): array
    {
        $tenants = Tenant::all();
        $results = [];

        /** @var Tenant $tenant */
        foreach ($tenants as $tenant) {
            $metrics = $this->collectTenantMetrics($tenant);

            $results[] = [
                'id' => $metrics->id,
                'name' => $metrics->name,
                'plan' => $metrics->plan,
                'total_orders' => $metrics->totalOrders,
                'month_orders' => $metrics->monthOrders,
                'total_products' => $metrics->totalProducts,
                'total_categories' => $metrics->totalCategories,
                'avg_review' => $metrics->avgReview,
                'owner' => $tenant->name,
                'email' => $tenant->email,
            ];
        }

        usort($results, fn (array $a, array $b) => $b['total_orders'] <=> $a['total_orders']);

        return $results;
    }

    private function collectTenantMetrics(Tenant $tenant): TenantMetrics
    {
        try {
            $metrics = resolve(TenancyManager::class)->withinTenant($tenant, fn () => [
                'total_orders' => DB::table('orders')->count(),
                'month_orders' => DB::table('orders')
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count(),
                'total_products' => DB::table('products')->count(),
                'total_categories' => DB::table('categories')->count(),
                'avg_review' => round((float) DB::table('reviews')->avg('rating'), 1),
            ]);
        } catch (\Throwable) {
            $metrics = [];
        }

        return new TenantMetrics(
            id: $tenant->id,
            name: $tenant->store_name ?? $tenant->name,
            plan: $tenant->plan?->value ?? 'trial',
            totalOrders: $metrics['total_orders'] ?? 0,
            monthOrders: $metrics['month_orders'] ?? 0,
            totalProducts: $metrics['total_products'] ?? 0,
            totalCategories: $metrics['total_categories'] ?? 0,
            avgReview: $metrics['avg_review'] ?? 0,
        );
    }

    /** @return array<string, mixed> */
    public function getLeaderboardSummaryStats(): array
    {
        $data = $this->getLeaderboardData();
        $totalOrders = array_sum(array_column($data, 'total_orders'));
        $count = count($data);

        return [
            'total_orders' => $totalOrders,
            'avg_orders' => $count > 0 ? round($totalOrders / $count, 1) : 0,
            'total_bakeries' => $count,
        ];
    }
}
