<?php

namespace App\Queries\Dashboard;

use App\Models\Operations\BlockedDate;
use App\Models\Orders\Order;
use App\Services\Inventory\CapacityCalculator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

final class CapacityOverviewQuery
{
    public function __construct(private CapacityCalculator $capacityCalculator) {}

    /** @return array{max: int, current: int, percentage: float|int} */
    public function forDate(Carbon $date): array
    {
        $maxOrders = $this->capacityCalculator->getMaxOrders($date);
        $currentOrders = Order::query()->whereDate('delivery_date', $date)->active()->count();

        return [
            'max' => $maxOrders,
            'current' => $currentOrders,
            'percentage' => $maxOrders > 0 ? min(100, round(($currentOrders / $maxOrders) * 100)) : 0,
        ];
    }

    /** @return list<array{date: string, reason: string}> */
    public function blockedDays(): array
    {
        $days = BlockedDate::query()
            ->whereBetween('date', [Date::today(), Date::today()->addDays(7)])
            ->where('is_all_day', true)
            ->orderBy('date')
            ->limit(3)
            ->get()
            ->map(fn (BlockedDate $blockedDate): array => [
                'date' => $blockedDate->date->format('M j'),
                'reason' => $blockedDate->reason ?? 'Closed',
            ])
            ->values()
            ->all();

        return array_values($days);
    }
}
