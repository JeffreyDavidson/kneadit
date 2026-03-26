<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use Filament\Widgets\Widget;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Date;

class CustomerInsightsWidget extends Widget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.customer-insights';

    public function getNewCustomersThisWeek(): int
    {
        return Customer::query()->where('created_at', '>=', Date::now()->startOfWeek())->count();
    }

    public function getRepeatCustomerRate(): float
    {
        $totalWithOrders = Customer::query()->whereHas('orders', fn (Builder $q) => $q->where('status', '!=', OrderStatus::Cancelled))->count();
        if ($totalWithOrders === 0) {
            return 0;
        }

        $repeat = Customer::query()->whereHas('orders', fn (Builder $q) => $q->where('status', '!=', OrderStatus::Cancelled), '>=', 2)->count();

        return round(($repeat / $totalWithOrders) * 100, 1);
    }

    /** @return array<string, mixed> */
    public function getAvgOrderValue(): array
    {
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
    }
}
