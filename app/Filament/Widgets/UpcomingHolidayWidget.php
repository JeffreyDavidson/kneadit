<?php

namespace App\Filament\Widgets;

use App\Models\Holiday;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UpcomingHolidayWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $holiday = Holiday::active()->upcoming()->first();

        if (!$holiday) {
            return [];
        }

        $orders = $holiday->orderCount();
        $daysUntil = $holiday->daysUntilDeadline();
        $deadlineLabel = $holiday->isDeadlinePassed()
            ? 'Deadline passed'
            : "Deadline in {$daysUntil}d";

        $description = $orders . ' orders';
        if ($holiday->max_orders) {
            $description .= " / {$holiday->max_orders} max";
        }
        $description .= " — {$deadlineLabel}";

        return [
            Stat::make("Next Holiday: {$holiday->name}", $holiday->date->format('M j'))
                ->icon('heroicon-o-calendar-days')
                ->color($daysUntil <= 3 && !$holiday->isDeadlinePassed() ? 'warning' : 'primary')
                ->description($description),
        ];
    }
}
