<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Operations\BlockedDate;
use App\Models\Orders\Order;
use App\Services\Inventory\CapacityCalculator;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Date;

class CapacityTodayWidget extends Widget
{
    use CachesWidgetData;

    protected static ?int $sort = 17;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.capacity-today-widget';

    /** @return array<string, mixed> */
    public function getCapacityData(Carbon $date): array
    {
        return $this->cached('capacity_' . $date->toDateString(), [300, 600], function () use ($date): array {
            $maxOrders = resolve(CapacityCalculator::class)->getMaxOrders($date);
            $currentOrders = Order::query()->whereDate('delivery_date', $date)
                ->active()
                ->count();

            $percentage = $maxOrders > 0 ? min(100, round(($currentOrders / $maxOrders) * 100)) : 0;

            return [
                'max' => $maxOrders,
                'current' => $currentOrders,
                'percentage' => $percentage,
            ];
        });
    }

    /** @return array<string, mixed> */
    public function getTodayCapacity(): array
    {
        return $this->getCapacityData(Date::today());
    }

    /** @return array<string, mixed> */
    public function getTomorrowCapacity(): array
    {
        return $this->getCapacityData(Date::tomorrow());
    }

    /** @return array<int, array<string, string>> */
    public function getBlockedDaysWarning(): array
    {
        return $this->cached('blocked_days_' . Date::today()->toDateString(), [1800, 3600], function (): array {
            return BlockedDate::query()->where('date', '>=', Date::today())
                ->where('date', '<=', Date::today()->addDays(7))
                ->where('is_all_day', true)
                ->orderBy('date')
                ->limit(3)
                ->get()
                ->map(fn (BlockedDate $b) => [
                    'date' => $b->date->format('M j'),
                    'reason' => $b->reason ?? 'Closed',
                ])
                ->all();
        });
    }

    protected function cachePrefix(): string
    {
        return 'capacity_today_widget';
    }
}
