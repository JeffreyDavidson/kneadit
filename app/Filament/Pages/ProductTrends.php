<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Filament\Traits\RequiresRole;
use App\Models\Category;
use App\Models\OrderItem;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;
use Livewire\Attributes\Url;

class ProductTrends extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'pro';

    protected string $view = 'filament.pages.product-trends';

    protected static ?string $title = 'Product Trends';

    protected static ?string $navigationLabel = 'Product Trends';

    protected static ?int $navigationSort = 6;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-arrow-trending-up';
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Tools';
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/admin' => 'Dashboard',
            static::getUrl() => 'Product Trends',
        ];
    }

    #[Url]
    public int $month = 0;

    #[Url]
    public int $year = 0;

    public function mount(): void
    {
        if ($this->month === 0) {
            $this->month = now()->month;
        }
        if ($this->year === 0) {
            $this->year = now()->year;
        }
    }

    public function previousMonth(): void
    {
        $date = Date::create($this->year, $this->month, 1)->subMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Date::create($this->year, $this->month, 1)->addMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function getTrendsDataProperty(): array
    {
        $currentStart = Date::create($this->year, $this->month, 1)->startOfMonth();
        $currentEnd = $currentStart->copy()->endOfMonth();
        $prevStart = $currentStart->copy()->subMonth()->startOfMonth();
        $prevEnd = $prevStart->copy()->endOfMonth();

        $currentCounts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', [OrderStatus::Cancelled->value])
            ->whereBetween('orders.created_at', [$currentStart, $currentEnd])
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) as total_qty')
            ->groupBy('order_items.product_id')
            ->pluck('total_qty', 'product_id')
            ->toArray();

        $prevCounts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', [OrderStatus::Cancelled->value])
            ->whereBetween('orders.created_at', [$prevStart, $prevEnd])
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) as total_qty')
            ->groupBy('order_items.product_id')
            ->pluck('total_qty', 'product_id')
            ->toArray();

        $categories = Category::with(['products' => fn (Builder $q) => $q->orderBy('sort_order')->orderBy('name')])->orderBy('sort_order')->get();

        $grouped = [];
        foreach ($categories as $category) {
            $products = [];
            foreach ($category->products as $product) {
                $current = (int) ($currentCounts[$product->id] ?? 0);
                $previous = (int) ($prevCounts[$product->id] ?? 0);

                if ($current === 0 && $previous === 0) {
                    continue;
                }

                $change = $previous > 0
                    ? round(($current - $previous) / $previous * 100, 1)
                    : ($current > 0 ? 100 : 0);

                $trend = $current > $previous ? 'up' : ($current < $previous ? 'down' : 'flat');

                $products[] = [
                    'name' => $product->name,
                    'current' => $current,
                    'previous' => $previous,
                    'change' => $change,
                    'trend' => $trend,
                ];
            }

            if (! empty($products)) {
                $grouped[] = [
                    'category' => $category->name,
                    'products' => $products,
                ];
            }
        }

        return $grouped;
    }

    public function getMonthLabelProperty(): string
    {
        return Date::create($this->year, $this->month, 1)->format('F Y');
    }

    public function getPrevMonthLabelProperty(): string
    {
        return Date::create($this->year, $this->month, 1)->subMonth()->format('M Y');
    }
}
