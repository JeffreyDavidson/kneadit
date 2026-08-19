<?php

namespace App\Queries\Customers;

use App\Enums\Orders\OrderStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Date;

final class CustomerInsightsQuery
{
    public function newCustomersThisWeek(): int
    {
        return Customer::query()->where('created_at', '>=', Date::now()->startOfWeek())->count();
    }

    public function repeatCustomerRate(): float
    {
        $hasActiveOrders = fn (Builder $query) => $query->whereNotIn('status', [OrderStatus::Cancelled]);
        $totalWithOrders = Customer::query()->whereHas('orders', $hasActiveOrders)->count();

        if ($totalWithOrders === 0) {
            return 0;
        }

        $repeatCustomers = Customer::query()->whereHas('orders', $hasActiveOrders, '>=', 2)->count();

        return round(($repeatCustomers / $totalWithOrders) * 100, 1);
    }

    /** @return array{value: float, trend: 'up'|'down'} */
    public function averageOrderValue(): array
    {
        $now = Date::now();
        $lastMonth = $now->copy()->subMonth();
        $thisMonthAverage = $this->averageForMonth($now->month, $now->year);
        $lastMonthAverage = $this->averageForMonth($lastMonth->month, $lastMonth->year);

        return [
            'value' => round($thisMonthAverage, 2),
            'trend' => $thisMonthAverage >= $lastMonthAverage ? 'up' : 'down',
        ];
    }

    private function averageForMonth(int $month, int $year): float
    {
        return (float) Order::query()->active()
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->avg('total');
    }
}
