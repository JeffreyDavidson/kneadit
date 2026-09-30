<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Operations\BlockedDate;
use App\Models\Orders\Order;
use App\Services\Inventory\CapacityCalculator;
use App\Services\Scheduling\BakeryClock;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class CapacityTodayWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    #[\Override]
    protected static ?int $sort = 17;

    #[\Override]
    protected string $view = 'filament.widgets.capacity-today-widget';

    /** @return array<string, mixed> */
    public function getCapacityData(Carbon $date): array
    {
        return $this->cached('capacity_'.$date->toDateString(), [300, 600], function () use ($date): array {
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
        return $this->getCapacityData(resolve(BakeryClock::class)->today());
    }

    /** @return array<string, mixed> */
    public function getTomorrowCapacity(): array
    {
        return $this->getCapacityData(resolve(BakeryClock::class)->today()->addDay());
    }

    /** @return array<string, mixed> */
    public function getDayAfterCapacity(): array
    {
        return $this->getCapacityData(resolve(BakeryClock::class)->today()->addDays(2));
    }

    /** @return array<int, array<string, string>> */
    public function getBlockedDaysWarning(): array
    {
        $today = resolve(BakeryClock::class)->today();

        return $this->cached('blocked_days_'.$today->toDateString(), [1800, 3600], fn (): array => BlockedDate::query()->whereDate('date', '>=', $today)
            ->whereDate('date', '<=', $today->copy()->addDays(7))
            ->where('is_all_day', true)
            ->orderBy('date')
            ->limit(3)
            ->get()
            ->map(fn (BlockedDate $b): array => [
                'date' => $b->date->format('M j'),
                'reason' => $b->reason ?? 'Closed',
            ])
            ->all());
    }

    protected function cachePrefix(): string
    {
        return 'capacity_today_widget';
    }
}
