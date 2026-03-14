<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StorefrontViewsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected function getStats(): array
    {
        $today = PageView::whereNull('product_id')
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        $yesterday = PageView::whereNull('product_id')
            ->whereBetween('created_at', [now()->subDay()->startOfDay(), now()->startOfDay()])
            ->count();

        $trend = $yesterday > 0
            ? round((($today - $yesterday) / $yesterday) * 100)
            : ($today > 0 ? 100 : 0);

        $description = $trend >= 0 ? "{$trend}% increase vs yesterday" : abs($trend).'% decrease vs yesterday';
        $icon = $trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $color = $trend >= 0 ? 'success' : 'danger';

        return [
            Stat::make('Storefront Views Today', number_format($today))
                ->description($description)
                ->descriptionIcon($icon)
                ->color($color)
                ->url('/admin/storefront-analytics'),
        ];
    }
}
