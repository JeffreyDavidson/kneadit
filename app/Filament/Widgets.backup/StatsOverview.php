<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalRevenue = Order::whereIn('payment_status', ['paid'])->sum('total');
        $activeProducts = Product::where('is_active', true)->count();

        return [
            Stat::make('Total Orders', $totalOrders)
                ->description('All time orders')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color('primary'),

            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->description('From paid orders')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Pending Orders', $pendingOrders)
                ->description('Awaiting confirmation')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingOrders > 5 ? 'warning' : 'primary'),

            Stat::make('Active Products', $activeProducts)
                ->description('Currently available')
                ->descriptionIcon('heroicon-o-cake')
                ->color('info'),
        ];
    }
}
