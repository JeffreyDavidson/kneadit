<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Orders\Order;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Number;

class WelcomeBannerWidget extends Widget
{
    use CachesWidgetData;

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.welcome-banner';

    public function getGreeting(): string
    {
        $hour = now()->hour;
        $name = Auth::user()->name ?? 'Baker';
        $firstName = explode(' ', $name)[0];

        return match (true) {
            $hour < 12 => "Good morning, {$firstName}!",
            $hour < 17 => "Good afternoon, {$firstName}!",
            default => "Good evening, {$firstName}!",
        };
    }

    public function getTodayDate(): string
    {
        return now()->format('l, F j, Y');
    }

    public function getOrdersToday(): int
    {
        return $this->cached('orders_today_' . Date::today()->toDateString(), [300, 600], fn (): int => Order::query()->whereDate('delivery_date', Date::today())->count());
    }

    public function getRevenueToday(): string
    {
        return $this->cached('revenue_today_' . Date::today()->toDateString(), [300, 600], fn (): string => (string) Number::currency(
            // orders.total is bigint cents (migration 2026_04_22_201500).
            (int) Order::query()->active()
                ->whereDate('delivery_date', Date::today())
                ->sum('total') / 100,
        ));
    }

    public function getPendingOrders(): int
    {
        return $this->cached('pending_orders', [300, 600], fn (): int => Order::query()->pending()->count());
    }

    protected function cachePrefix(): string
    {
        return 'welcome_banner';
    }
}
