<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

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
        return Order::whereDate('delivery_date', Carbon::today())->count();
    }

    public function getRevenueToday(): string
    {
        return number_format(
            (float) Order::where('status', '!=', 'cancelled')
                ->whereDate('delivery_date', Carbon::today())
                ->sum('total'),
            2
        );
    }

    public function getPendingOrders(): int
    {
        return Order::where('status', 'pending')->count();
    }
}
