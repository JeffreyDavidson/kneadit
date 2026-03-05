<?php

namespace App\Filament\Widgets;

use App\Models\CapacityLimit;
use App\Models\Order;
use App\Models\WaitlistEntry;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();

        return [
            Stat::make("Today's Orders", Order::whereDate('requested_date', $today)->count())
                ->icon('heroicon-o-shopping-bag')
                ->color('primary'),

            Stat::make('Pending Orders', Order::where('status', 'pending')->count())
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make("This Week's Revenue", '$' . number_format(
                (float) Order::where('status', '!=', 'cancelled')
                    ->whereBetween('requested_date', [$weekStart, Carbon::now()->endOfWeek()])
                    ->sum('total'),
                2
            ))
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('Total Customers', Order::distinct('customer_email')->count('customer_email'))
                ->icon('heroicon-o-users')
                ->color('info'),

            ...collect([WaitlistEntry::waiting()->where('requested_date', '>=', $today)->count()])
                ->filter()
                ->map(fn (int $count) => Stat::make('Waitlist', $count . ' ' . str('person')->plural($count))
                    ->icon('heroicon-o-queue-list')
                    ->color('warning')
                    ->description('Waiting for upcoming dates')
                    ->url(route('filament.admin.resources.waitlist-entries.index')))
                ->all(),

            ...collect([$today, $today->copy()->addDay()])
                ->map(function (Carbon $date) {
                    $usage = CapacityLimit::usagePercent($date);
                    if ($usage === null || $usage < 80) return null;
                    $label = $date->isToday() ? 'Today' : 'Tomorrow';
                    $limit = CapacityLimit::forDate($date);
                    if ($limit?->is_blocked) {
                        return Stat::make("$label Capacity", 'BLOCKED')
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->description('Day is blocked for orders');
                    }
                    $remaining = CapacityLimit::remainingSlots($date);
                    return Stat::make("$label Capacity", round($usage) . '% full')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color($usage >= 100 ? 'danger' : 'warning')
                        ->description($remaining . ' slots remaining');
                })
                ->filter()
                ->all(),
        ];
    }
}
