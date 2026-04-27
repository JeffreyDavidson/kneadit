<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Operations\Holiday;
use App\Models\Orders\Order;
use App\Presenters\HolidayPresenter;
use Filament\Widgets\Widget;

class UpcomingHolidayWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 2;

    protected string $view = 'filament.widgets.upcoming-holiday';

    /** @return array<string, mixed>|null */
    public function getHolidayData(): ?array
    {
        return $this->cached('data_' . now()->format('Y-m-d'), [1800, 3600], function (): ?array {
            try {
                /** @var Holiday|null $holiday */
                $holiday = Holiday::query()->active()->upcoming()->first();
            } catch (\Exception) {
                return null;
            }

            if (! $holiday) {
                return null;
            }

            $orders = Order::query()->whereDate('delivery_date', $holiday->date)->count();
            $presenter = HolidayPresenter::for($holiday);
            $daysUntil = $presenter->daysUntilDeadline();
            $deadlinePassed = $presenter->isDeadlinePassed();

            return [
                'name' => $holiday->name,
                'date' => $holiday->date->format('M j'),
                'orders' => $orders,
                'max_orders' => $holiday->max_orders,
                'days_until_deadline' => $daysUntil,
                'deadline_passed' => $deadlinePassed,
                'is_urgent' => $daysUntil <= 3 && ! $deadlinePassed,
            ];
        });
    }

    protected function cachePrefix(): string
    {
        return 'upcoming_holiday';
    }
}
