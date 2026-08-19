<?php

namespace App\Filament\Widgets;

use App\Enums\Filament\WidgetSize;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Queries\Orders\UpcomingOrdersQuery;
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
        return resolve(UpcomingOrdersQuery::class)->hasWithinDays(7);
    }

    /**
     * @return array<string, array{label: string, orders: list<array{id: int, number: string, customer: string, items: int, total: string, time: string, status: \App\Enums\Orders\OrderStatus}>}>
     */
    public function getUpcomingOrders(): array
    {
        $daysAhead = $this->daysAhead();

        return $this->cached("upcoming_{$daysAhead}_" . Date::today()->toDateString(), [600, 1200], fn (): array => resolve(UpcomingOrdersQuery::class)->get($daysAhead));
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
            WidgetSize::Large, WidgetSize::ExtraLarge => 7,   // full week
        };
    }
}
