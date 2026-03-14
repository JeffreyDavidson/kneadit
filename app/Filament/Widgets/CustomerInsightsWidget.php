<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class CustomerInsightsWidget extends Widget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.customer-insights';

    public function getNewCustomersThisWeek(): int
    {
        return Customer::where('created_at', '>=', Carbon::now()->startOfWeek())->count();
    }

    public function getRepeatCustomerRate(): float
    {
        $totalWithOrders = Customer::whereHas('orders', fn ($q) => $q->where('status', '!=', 'cancelled'))->count();
        if ($totalWithOrders === 0) {
            return 0;
        }

        $repeat = Customer::whereHas('orders', fn ($q) => $q->where('status', '!=', 'cancelled'), '>=', 2)->count();

        return round(($repeat / $totalWithOrders) * 100, 1);
    }

    public function getAvgOrderValue(): array
    {
        $thisMonth = (float) Order::where('status', '!=', 'cancelled')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->avg('total') ?? 0;

        $lastMonth = (float) Order::where('status', '!=', 'cancelled')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->avg('total') ?? 0;

        return [
            'value' => round($thisMonth, 2),
            'trend' => $thisMonth >= $lastMonth ? 'up' : 'down',
        ];
    }
}
