<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Date;

class UpcomingOrdersWidget extends Widget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 1];

    protected string $view = 'filament.widgets.upcoming-orders';

    /** @return array<string, mixed> */
    public function getUpcomingOrders(): array
    {
        $today = Date::today();
        $endDate = $today->copy()->addDays(3);

        $orders = Order::with('customer')->withCount('orderItems')
            ->active()
            ->whereBetween('delivery_date', [$today, $endDate])
            ->oldest('delivery_date')
            ->orderBy('delivery_time')
            ->get();

        $grouped = [];
        foreach ($orders as $order) {
            $date = $order->delivery_date?->format('Y-m-d');
            $label = match (true) {
                $order->delivery_date?->isToday() => 'Today',
                $order->delivery_date?->isTomorrow() => 'Tomorrow',
                default => $order->delivery_date?->format('l, M j'),
            };

            $grouped[$date] ??= ['label' => $label, 'orders' => []];
            $grouped[$date]['orders'][] = [
                'id' => $order->id,
                'number' => $order->order_number,
                'customer' => $order->customer->name ?? 'Walk-in',
                'items' => $order->order_items_count,
                'total' => number_format($order->total, 2),
                'time' => $order->delivery_time?->format('g:i A') ?? '',
                'status' => $order->status,
            ];
        }

        return $grouped;
    }
}
