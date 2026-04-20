<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Operations\Holiday;
use App\Models\Orders\Order;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UpcomingHolidayWidget extends BaseWidget
{
    use CachesWidgetData;

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        return $this->cached('stats_' . now()->format('Y-m-d'), [1800, 3600], function (): array {
            try {
                /** @var Holiday|null $holiday */
                $holiday = Holiday::query()->active()->upcoming()->first();
            } catch (\Exception $e) {
                return [];
            }

            if (! $holiday) {
                return [];
            }

            $orders = Order::query()->whereDate('delivery_date', $holiday->date)->count();
            $daysUntil = $holiday->days_until_deadline;
            $deadlineLabel = $holiday->is_deadline_passed
                ? 'Deadline passed'
                : "Deadline in {$daysUntil}d";

            $description = $orders . ' orders';
            if ($holiday->max_orders) {
                $description .= " / {$holiday->max_orders} max";
            }
            $description .= " — {$deadlineLabel}";

            return [
                Stat::make("Next Holiday: {$holiday->name}", $holiday->date->format('M j'))
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->color($daysUntil <= 3 && ! $holiday->is_deadline_passed ? 'warning' : 'primary')
                    ->description($description),
            ];
        });
    }

    protected function cachePrefix(): string
    {
        return 'upcoming_holiday';
    }
}
