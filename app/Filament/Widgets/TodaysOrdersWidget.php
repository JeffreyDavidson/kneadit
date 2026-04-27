<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Orders\Order;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Date;

class TodaysOrdersWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 2;

    protected string $view = 'filament.widgets.todays-orders';

    /** @return array<int, array<string, mixed>> */
    public function getSlots(): array
    {
        return $this->cached('main_' . Date::today()->toDateString(), [60, 120], fn (): array => Order::query()
            ->whereDate('delivery_date', Date::today())
            ->orderBy('delivery_time')
            ->get()
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'time' => $order->delivery_time?->format('g:i A') ?? '—',
                'customer' => $order->customer_name,
                'total' => $order->total->formatted(),
                'total_cents' => $order->total->cents(),
                'status' => $order->status,
                'dot_color' => $order->status->funnelDotColor(),
            ])
            ->all());
    }

    public function getRevenueToday(): string
    {
        $cents = (int) array_sum(array_column($this->getSlots(), 'total_cents'));

        return '$' . number_format($cents / 100, 2);
    }

    public function getViewAllUrl(): string
    {
        return route('filament.admin.resources.orders.index');
    }

    protected function cachePrefix(): string
    {
        return 'todays_orders';
    }
}
