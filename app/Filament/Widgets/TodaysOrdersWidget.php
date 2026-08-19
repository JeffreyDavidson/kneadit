<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
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

    /**
     * Hide entirely when no orders are due today — the
     * "Enjoy the quiet" empty state was just dead space.
     * Reappears the moment something with delivery_date = today exists.
     */
    public static function canView(): bool
    {
        return Order::query()->whereDate('delivery_date', Date::today())->exists();
    }

    /** @return array<int, array{id: int, order_number: string, time: string, customer: string, total: string, total_cents: int, status: \App\Enums\Orders\OrderStatus, dot_color: string}> */
    public function getOrderRows(): array
    {
        return $this->cached('main_' . Date::today()->toDateString(), [60, 120], fn (): array => Order::query()
            ->whereDate('delivery_date', Date::today())
            ->orderBy('delivery_time')
            ->get()
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'time' => $order->delivery_time?->format('g:i A') ?? '—',
                'customer' => is_string($order->getAttribute('customer_name')) ? $order->getAttribute('customer_name') : 'Unknown Customer',
                'total' => $order->total->formatted(),
                'total_cents' => $order->total->cents(),
                'status' => $order->status,
                'dot_color' => $order->status->funnelDotColor(),
            ])
            ->all());
    }

    public function getRevenueToday(): string
    {
        $cents = array_sum(array_column($this->getOrderRows(), 'total_cents'));

        return '$' . number_format($cents / 100, 2);
    }

    public function getViewAllUrl(): string
    {
        return OrderResource::getUrl('index');
    }

    protected function cachePrefix(): string
    {
        return 'todays_orders';
    }
}
