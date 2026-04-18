<?php

namespace App\Filament\Widgets;

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Filament\Widgets\Widget;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Date;

class CustomerInsightsWidget extends Widget
{
    use CachesWidgetData;

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.customer-insights';

    public function getNewCustomersThisWeek(): int
    {
        $weekKey = Date::now()->startOfWeek()->format('Y-W');

        return $this->cached("new_{$weekKey}", [900, 1800], fn (): int => Customer::query()->where('created_at', '>=', Date::now()->startOfWeek())->count());
    }

    public function getRepeatCustomerRate(): float
    {
        return $this->cached('repeat', [3600, 7200], function (): float {
            $totalWithOrders = Customer::query()->whereHas('orders', fn (Builder $q) => $q->where('status', '!=', OrderStatus::Cancelled))->count();
            if ($totalWithOrders === 0) {
                return 0;
            }

            $repeat = Customer::query()->whereHas('orders', fn (Builder $q) => $q->where('status', '!=', OrderStatus::Cancelled), '>=', 2)->count();

            return round(($repeat / $totalWithOrders) * 100, 1);
        });
    }

    /** @return array<string, mixed> */
    public function getAvgOrderValue(): array
    {
        $monthKey = now()->format('Y-m');

        return $this->cached("aov_{$monthKey}", [900, 1800], function (): array {
            $thisMonth = (float) Order::query()->active()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->avg('total');

            $lastMonth = (float) Order::query()->active()
                ->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->avg('total');

            return [
                'value' => round($thisMonth, 2),
                'trend' => $thisMonth >= $lastMonth ? 'up' : 'down',
            ];
        });
    }

    protected function cachePrefix(): string
    {
        return 'customer_insights';
    }
}
