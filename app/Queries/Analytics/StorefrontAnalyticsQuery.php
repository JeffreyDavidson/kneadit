<?php

namespace App\Queries\Analytics;

use App\DataTransferObjects\Analytics\ConversionFunnelStep;
use App\DataTransferObjects\Analytics\DailyPageViewCount;
use App\DataTransferObjects\Analytics\PageViewCount;
use App\DataTransferObjects\Analytics\TopViewedProduct;
use App\Models\Engagement\PageView;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StorefrontAnalyticsQuery
{
    public function __construct(
        private readonly ?Carbon $startDate = null,
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
        if ($this->startDate instanceof Carbon) {
            $ordersQuery->where('created_at', '>=', $this->startDate);
        }

        return round(($ordersQuery->count() / $orderPageViews) * 100, 1);
    }

    /** @return Collection<int, PageViewCount> */
    public function pageViewsByPage(): Collection
    {
        return $this->baseQuery()
            ->select('page', DB::raw('COUNT(*) as views'))
            ->groupBy('page')
            ->orderByDesc('views')
            ->toBase()
            ->get()
            ->map(fn (object $row): PageViewCount => new PageViewCount(
                page: Arr::string(['page' => $row->page], 'page'),
                views: Arr::integer(['views' => $row->views], 'views', 0),
            ));
    }

    /** @return Collection<int, DailyPageViewCount> */
    public function dailyTrend(int $days = 30): Collection
    {
        $start = now()->subDays($days);
        $counts = DateCountQuery::count(
            PageView::query()->whereNull('product_id'),
            'created_at',
            $start,
            now(),
        );

        return collect($counts)
            ->sortKeys()
            ->map(fn (int $views, string $date): DailyPageViewCount => new DailyPageViewCount(
                date: $date,
                views: $views,
            ))
            ->values();
    }

    /** @return Collection<int, TopViewedProduct> */
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
            fn (PageView $row): TopViewedProduct => new TopViewedProduct(
                name: is_string($products[$row->product_id] ?? null) ? $products[$row->product_id] : 'Unknown',
                views: (int) $row->views,
            ),
        );
    }

    /** @return list<ConversionFunnelStep> */
    public function conversionFunnel(): array
    {
        $homeViews = $this->uniqueSessionsForPage('home');
        $menuViews = $this->uniqueSessionsForPage('menu');
        $orderViews = $this->uniqueSessionsForPage('order');

        $ordersQuery = Order::query();
        if ($this->startDate instanceof Carbon) {
            $ordersQuery->where('created_at', '>=', $this->startDate);
        }
        $completedOrders = $ordersQuery->count();

        $counts = [
            ['label' => 'Home', 'count' => $homeViews],
            ['label' => 'Menu', 'count' => $menuViews],
            ['label' => 'Order Page', 'count' => $orderViews],
            ['label' => 'Orders Placed', 'count' => $completedOrders],
        ];

        $maxVal = max(1, max(array_column($counts, 'count')) ?: 1);
        $funnel = [];

        foreach ($counts as $i => $step) {
            $dropoff = $i > 0 && $counts[$i - 1]['count'] > 0
                ? max(0, round((1 - $step['count'] / $counts[$i - 1]['count']) * 100, 1))
                : null;

            $funnel[] = new ConversionFunnelStep(
                label: $step['label'],
                count: $step['count'],
                percentage: round(($step['count'] / $maxVal) * 100),
                dropoff: $dropoff,
            );
        }

        return $funnel;
    }

    /** @return Builder<PageView> */
    private function baseQuery(): Builder
    {
        $query = PageView::query()->whereNull('product_id');

        if ($this->startDate instanceof Carbon) {
            $query->where('created_at', '>=', $this->startDate);
        }

        return $query;
    }

    /** @return Builder<PageView> */
    private function productQuery(): Builder
    {
        $query = PageView::query()->whereNotNull('product_id');

        if ($this->startDate instanceof Carbon) {
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
