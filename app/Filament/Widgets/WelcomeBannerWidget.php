<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class WelcomeBannerWidget extends Widget
{
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
        return Order::query()->whereDate('delivery_date', Date::today())->count();
    }

    public function getRevenueToday(): string
    {
        return number_format(
            (float) Order::query()->where('status', '!=', OrderStatus::Cancelled)
                ->whereDate('delivery_date', Date::today())
                ->sum('total'),
            2,
        );
    }

    public function getPendingOrders(): int
    {
        return Order::query()->where('status', OrderStatus::Pending)->count();
    }
}
