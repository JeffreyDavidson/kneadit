<?php

namespace App\Services\Analytics;

use App\Enums\Orders\OrderStatus;
use App\Models\Inventory\Category;
use App\Models\Orders\OrderItem;
use App\ValueObjects\DateRange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

class ProductTrendsService
{
    /** @return array<int, array<string, mixed>> */
    public function calculate(int $year, int $month): array
    {
        $currentRange = DateRange::forMonth($year, $month);
        $prevDate = Date::create($year, $month, 1)->subMonth();
        $prevRange = DateRange::forMonth($prevDate->year, $prevDate->month);

        $currentCounts = $this->getProductCounts($currentRange);
        $prevCounts = $this->getProductCounts($prevRange);

        return $this->groupByCategory($currentCounts, $prevCounts);
    }

    /** @return array<int, int> */
    private function getProductCounts(DateRange $range): array
    {
        return OrderItem::query()->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', [OrderStatus::Cancelled->value])
            ->whereBetween('orders.created_at', $range->toArray())
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) as total_qty')
            ->groupBy('order_items.product_id')
            ->pluck('total_qty', 'product_id')
            ->toArray();
    }

    /**
     * @param array<int, int> $currentCounts
     * @param array<int, int> $prevCounts
     * @return array<int, array<string, mixed>>
     */
    private function groupByCategory(array $currentCounts, array $prevCounts): array
    {
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

                $products[] = [
                    'name' => $product->name,
                    'current' => $current,
                    'previous' => $previous,
                    'change' => $change,
                    'trend' => $current > $previous ? 'up' : ($current < $previous ? 'down' : 'flat'),
                ];
            }

            if (! empty($products)) {
                $grouped[] = ['category' => $category->name, 'products' => $products];
            }
        }

        return $grouped;
    }
}
