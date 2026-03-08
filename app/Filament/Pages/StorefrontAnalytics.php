<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Models\Order;
use App\Models\PageView;
use App\Models\Product;
use App\Traits\HasPlanGating;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StorefrontAnalytics extends Page
{
    use HasPlanGating, RequiresRole;

    protected static string $requiredPlan = 'pro';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Storefront Analytics';

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.storefront-analytics';

    public string $period = 'week';

    public function mount(): void
    {
        $this->period = request()->query('period', 'week');
    }

    protected function getStartDate(): ?Carbon
    {
        return match ($this->period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'all' => null,
            default => now()->startOfWeek(),
        };
    }

    protected function baseQuery()
    {
        $query = PageView::query();
        $start = $this->getStartDate();
        if ($start) {
            $query->where('created_at', '>=', $start);
        }

        return $query;
    }

    public function getTotalViews(): int
    {
        return $this->baseQuery()->whereNull('product_id')->count();
    }

    public function getUniqueVisitors(): int
    {
        return $this->baseQuery()->whereNull('product_id')->distinct('session_id')->count('session_id');
    }

    public function getMostPopularPage(): string
    {
        $page = $this->baseQuery()
            ->whereNull('product_id')
            ->select('page', DB::raw('COUNT(*) as views'))
            ->groupBy('page')
            ->orderByDesc('views')
            ->first();

        return $page?->page ?? '—';
    }

    public function getConversionRate(): float
    {
        $orderPageViews = $this->baseQuery()->whereNull('product_id')->where('page', 'order')->count();
        if ($orderPageViews === 0) {
            return 0;
        }

        $start = $this->getStartDate();
        $ordersQuery = Order::query();
        if ($start) {
            $ordersQuery->where('created_at', '>=', $start);
        }
        $completedOrders = $ordersQuery->count();

        return round(($completedOrders / $orderPageViews) * 100, 1);
    }

    public function getPageViewsChart(): Collection
    {
        $data = $this->baseQuery()
            ->whereNull('product_id')
            ->select('page', DB::raw('COUNT(*) as views'))
            ->groupBy('page')
            ->orderByDesc('views')
            ->get();

        return $data;
    }

    public function getDailyTrend(): Collection
    {
        $start = now()->subDays(30)->startOfDay();

        return PageView::where('created_at', '>=', $start)
            ->whereNull('product_id')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as views'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function getTopProducts(): Collection
    {
        $data = $this->baseQuery()
            ->whereNotNull('product_id')
            ->select('product_id', DB::raw('COUNT(*) as views'))
            ->groupBy('product_id')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $products = Product::whereIn('id', $data->pluck('product_id'))->pluck('name', 'id');

        return $data->map(fn ($row) => (object) [
            'name' => $products[$row->product_id] ?? 'Unknown',
            'views' => $row->views,
        ]);
    }

    public function getConversionFunnel(): array
    {
        $start = $this->getStartDate();
        $base = PageView::query()->whereNull('product_id');
        if ($start) {
            $base->where('created_at', '>=', $start);
        }

        $homeViews = (clone $base)->where('page', 'home')->count();
        $menuViews = (clone $base)->where('page', 'menu')->count();
        $orderViews = (clone $base)->where('page', 'order')->count();

        $ordersQuery = Order::query();
        if ($start) {
            $ordersQuery->where('created_at', '>=', $start);
        }
        $completedOrders = $ordersQuery->count();

        $funnel = [
            ['label' => 'Home', 'count' => $homeViews],
            ['label' => 'Menu', 'count' => $menuViews],
            ['label' => 'Order Page', 'count' => $orderViews],
            ['label' => 'Completed Orders', 'count' => $completedOrders],
        ];

        $maxCount = max(array_column($funnel, 'count'), [1]);
        $maxVal = max($maxCount, 1);

        foreach ($funnel as $i => &$step) {
            $step['percentage'] = $maxVal > 0 ? round(($step['count'] / $maxVal) * 100) : 0;
            $step['dropoff'] = $i > 0 && $funnel[$i - 1]['count'] > 0
                ? round((1 - $step['count'] / $funnel[$i - 1]['count']) * 100, 1)
                : null;
        }

        return $funnel;
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }
}
