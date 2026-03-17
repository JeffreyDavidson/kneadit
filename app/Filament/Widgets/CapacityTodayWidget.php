<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\BlockedDate;
use App\Models\CapacityLimit;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Date;

class CapacityTodayWidget extends Widget
{
    protected static ?int $sort = 17;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.capacity-today-widget';

    public function getCapacityData(Carbon $date): array
    {
        $maxOrders = CapacityLimit::getMaxOrders($date);
        $currentOrders = Order::whereDate('delivery_date', $date)
            ->whereNotIn('status', [OrderStatus::Cancelled])
            ->count();

        $percentage = $maxOrders > 0 ? min(100, round(($currentOrders / $maxOrders) * 100)) : 0;

        return [
            'max' => $maxOrders,
            'current' => $currentOrders,
            'percentage' => $percentage,
        ];
    }

    public function getTodayCapacity(): array
    {
        return $this->getCapacityData(Date::today());
    }

    public function getTomorrowCapacity(): array
    {
        return $this->getCapacityData(Date::tomorrow());
    }

    public function getBlockedDaysWarning(): array
    {
        return BlockedDate::where('date', '>=', Date::today())
            ->where('date', '<=', Date::today()->addDays(7))
            ->where('is_all_day', true)
            ->orderBy('date')
            ->limit(3)
            ->get()
            ->map(fn ($b) => [
                'date' => $b->date->format('M j'),
                'reason' => $b->reason ?? 'Closed',
            ])
            ->toArray();
    }
}
