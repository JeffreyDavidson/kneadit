<?php

namespace App\Filament\Widgets;

use App\Enums\Filament\WidgetSize;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Orders\Order;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Date;

class UpcomingOrdersWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 6;

    protected string $view = 'filament.widgets.upcoming-orders';

    /**
     * Hide entirely when there's nothing in the next week — empty
     * "No upcoming orders" tile is dead space on a busy dashboard.
     * Once an order with a delivery date in the next 7 days exists,
     * the widget reappears at its configured size.
     */
    public static function canView(): bool
    {
        return Order::query()
            ->active()
            ->whereBetween('delivery_date', [Date::today(), Date::today()->copy()->addDays(7)])
            ->exists();
    }

    /** @return array<string, mixed> */
    public function getUpcomingOrders(): array
    {
        $daysAhead = $this->daysAhead();

        return $this->cached("upcoming_{$daysAhead}_" . Date::today()->toDateString(), [600, 1200], function () use ($daysAhead): array {
            $today = Date::today();
            $endDate = $today->copy()->addDays($daysAhead);

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
                    'total' => $order->total->formatted(),
                    'time' => $order->delivery_time?->format('g:i A') ?? '',
                    'status' => $order->status,
                ];
            }

            return $grouped;
        });
    }

    protected function cachePrefix(): string
    {
        return 'upcoming_orders_widget';
    }

    private function daysAhead(): int
    {
        return match ($this->size()) {
            WidgetSize::Small => 3,   // next 3 days (matches WidgetMeta description, preserves original behavior)
            WidgetSize::Medium => 5,
            WidgetSize::Large => 7,   // full week
        };
    }
}
