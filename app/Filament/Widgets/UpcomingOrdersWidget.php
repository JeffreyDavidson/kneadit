<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class UpcomingOrdersWidget extends Widget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 1];

    protected string $view = 'filament.widgets.upcoming-orders';

    public function getUpcomingOrders(): array
    {
        $today = Carbon::today();
        $endDate = $today->copy()->addDays(3);

        $orders = Order::with('customer')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('delivery_date', [$today, $endDate])
            ->orderBy('delivery_date')
            ->orderBy('delivery_time')
            ->get();

        $grouped = [];
        foreach ($orders as $order) {
            $date = $order->delivery_date->format('Y-m-d');
            $label = match (true) {
                $order->delivery_date->isToday() => 'Today',
                $order->delivery_date->isTomorrow() => 'Tomorrow',
                default => $order->delivery_date->format('l, M j'),
            };

            $grouped[$date] ??= ['label' => $label, 'orders' => []];
            $grouped[$date]['orders'][] = [
                'id' => $order->id,
                'number' => $order->order_number,
                'customer' => $order->customer?->name ?? 'Walk-in',
                'items' => $order->orderItems()->count(),
                'total' => number_format($order->total, 2),
                'time' => $order->delivery_time?->format('g:i A') ?? '',
                'status' => $order->status,
            ];
        }

        return $grouped;
    }
}
