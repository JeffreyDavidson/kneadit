<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PageView;
use App\Models\WaitlistEntry;
use App\Services\Inventory\CapacityCalculator;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return Cache::flexible('stats_overview_' . (tenant()?->getTenantKey() ?? 'none'), [60, 120], function (): array {
            $today = Date::today();
            $weekStart = Date::now()->startOfWeek();

            return [
                Stat::make("Today's Orders", Order::query()->whereDate('delivery_date', $today)->count())
                    ->icon('heroicon-o-shopping-bag')
                    ->color('primary'),

                Stat::make('Pending Orders', Order::query()->where('status', OrderStatus::Pending)->count())
                    ->icon('heroicon-o-clock')
                    ->color('warning'),

                Stat::make("This Week's Revenue", Number::currency(
                    (float) Order::query()->active()
                        ->whereBetween('delivery_date', [$weekStart, Date::now()->endOfWeek()])
                        ->sum('total'),
                ))
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success'),

                Stat::make('Avg Order Value', Number::currency(
                    (float) Order::query()->active()
                        ->whereBetween('delivery_date', [$weekStart, Date::now()->endOfWeek()])
                        ->avg('total'),
                ))
                    ->icon('heroicon-o-receipt-percent')
                    ->color('info'),

                Stat::make('Total Customers', Order::query()->distinct('customer_email')->count('customer_email'))
                    ->icon('heroicon-o-users')
                    ->color('info'),

                Stat::make('Storefront Views Today', Number::format(
                    PageView::query()->whereNull('product_id')
                        ->where('created_at', '>=', $today)
                        ->count(),
                ))
                    ->icon('heroicon-o-eye')
                    ->color('primary'),

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
                        $usage = resolve(CapacityCalculator::class)->usagePercent($date);
                        if ($usage < 80) {
                            return null;
                        }
                        $label = $date->isToday() ? 'Today' : 'Tomorrow';
                        $limit = resolve(CapacityCalculator::class)->forDate($date);
                        if ($limit?->is_blocked) {
                            return Stat::make("$label Capacity", 'BLOCKED')
                                ->icon('heroicon-o-x-circle')
                                ->color('danger')
                                ->description('Day is blocked for orders');
                        }
                        $remaining = resolve(CapacityCalculator::class)->remainingSlots($date);

                        return Stat::make("$label Capacity", round($usage) . '% full')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->color($usage >= 100 ? 'danger' : 'warning')
                            ->description($remaining . ' slots remaining');
                    })
                    ->filter()
                    ->all(),
            ];
        });
    }
}
