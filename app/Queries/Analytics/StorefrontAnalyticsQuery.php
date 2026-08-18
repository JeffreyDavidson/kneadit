<?php

namespace App\Queries\Analytics;

use App\Models\Engagement\PageView;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StorefrontAnalyticsQuery
{
    public function __construct(
        private ?Carbon $startDate = null,
    ) {}

    public function totalViews(): int
    {
        return $this->baseQuery()->count();
    }

    public function uniqueVisitors(): int
    {
        return $this->baseQuery()->distinct('session_id')->count('session_id');
    }

    public function mostPopularPage(): string
    {
        $page = $this->baseQuery()
            ->select('page', DB::raw('COUNT(*) as views'))
            ->groupBy('page')
            ->orderByDesc('views')
            ->first();

        return $page->page ?? '—';
    }

    public function conversionRate(): float
    {
        $orderPageViews = $this->baseQuery()->where('page', 'order')->count();

        if ($orderPageViews === 0) {
            return 0;
        }

        $ordersQuery = Order::query();
        if ($this->startDate) {
            $ordersQuery->where('created_at', '>=', $this->startDate);
        }

        return round(($ordersQuery->count() / $orderPageViews) * 100, 1);
    }

    /** @return Collection<int, mixed> */
    public function pageViewsByPage(): Collection
    {
        return $this->baseQuery()
            ->select('page', DB::raw('COUNT(*) as views'))
            ->groupBy('page')
            ->orderByDesc('views')
            ->get();
    }

    /** @return Collection<int, mixed> */
    public function dailyTrend(int $days = 30): Collection
    {
        return PageView::query()
            ->where('created_at', '>=', now()->subDays($days)->startOfDay())
            ->whereNull('product_id')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as views'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /** @return Collection<int, mixed> */
    public function topProducts(int $limit = 10): Collection
    {
        $data = $this->productQuery()
            ->select('product_id', DB::raw('COUNT(*) as views'))
            ->groupBy('product_id')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();

        $products = Product::query()->whereIn('id', $data->pluck('product_id'))->pluck('name', 'id');

        return $data->map(
            fn (PageView $row) => (object) [
                'name' => $products[$row->product_id] ?? 'Unknown',
                'views' => $row->views,
            ],
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function conversionFunnel(): array
    {
        $homeViews = $this->uniqueSessionsForPage('home');
        $menuViews = $this->uniqueSessionsForPage('menu');
        $orderViews = $this->uniqueSessionsForPage('order');

        $ordersQuery = Order::query();
        if ($this->startDate) {
            $ordersQuery->where('created_at', '>=', $this->startDate);
        }
        $completedOrders = $ordersQuery->count();

        $funnel = [
            ['label' => 'Home', 'count' => $homeViews],
            ['label' => 'Menu', 'count' => $menuViews],
            ['label' => 'Order Page', 'count' => $orderViews],
            ['label' => 'Orders Placed', 'count' => $completedOrders],
        ];

        $maxVal = max(1, max(array_column($funnel, 'count')) ?: 1);

        foreach ($funnel as $i => &$step) {
            $step['percentage'] = round(($step['count'] / $maxVal) * 100);
            $step['dropoff'] = $i > 0 && $funnel[$i - 1]['count'] > 0
                ? max(0, round((1 - $step['count'] / $funnel[$i - 1]['count']) * 100, 1))
                : null;
        }

        return $funnel;
    }

    /** @return Builder<PageView> */
    private function baseQuery(): Builder
    {
        $query = PageView::query()->whereNull('product_id');

        if ($this->startDate) {
            $query->where('created_at', '>=', $this->startDate);
        }

        return $query;
    }

    /** @return Builder<PageView> */
    private function productQuery(): Builder
    {
        $query = PageView::query()->whereNotNull('product_id');

        if ($this->startDate) {
            $query->where('created_at', '>=', $this->startDate);
        }

        return $query;
    }

    private function uniqueSessionsForPage(string $page): int
    {
        return (clone $this->baseQuery())
            ->where('page', $page)
            ->distinct()
            ->count('session_id');
    }
}
