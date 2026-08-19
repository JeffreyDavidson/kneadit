<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Queries\Dashboard\CapacityOverviewQuery;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Date;

class CapacityTodayWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 17;

    protected string $view = 'filament.widgets.capacity-today-widget';

    /** @return array{max: int, current: int, percentage: float|int} */
    public function getCapacityData(Carbon $date): array
    {
        return $this->cached('capacity_' . $date->toDateString(), [300, 600], fn (): array => resolve(CapacityOverviewQuery::class)->forDate($date));
    }

    /** @return array{max: int, current: int, percentage: float|int} */
    public function getTodayCapacity(): array
    {
        return $this->getCapacityData(Date::today());
    }

    /** @return array{max: int, current: int, percentage: float|int} */
    public function getTomorrowCapacity(): array
    {
        return $this->getCapacityData(Date::tomorrow());
    }

    /** @return array{max: int, current: int, percentage: float|int} */
    public function getDayAfterCapacity(): array
    {
        return $this->getCapacityData(Date::today()->copy()->addDays(2));
    }

    /** @return list<array{date: string, reason: string}> */
    public function getBlockedDaysWarning(): array
    {
        return $this->cached('blocked_days_' . Date::today()->toDateString(), [1800, 3600], fn (): array => resolve(CapacityOverviewQuery::class)->blockedDays());
    }

    protected function cachePrefix(): string
    {
        return 'capacity_today_widget';
    }
}
