<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AtRiskCustomersWidget;
use App\Filament\Widgets\CustomerInsightsWidget;
use App\Filament\Widgets\LowStockWidget;
use App\Filament\Widgets\OrderFunnelWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\StorefrontViewsWidget;
use App\Filament\Widgets\TopProductsWidget;
use App\Filament\Widgets\UpcomingOrdersWidget;
use App\Filament\Widgets\WelcomeBannerWidget;
use App\Traits\HasPlanGating;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    use HasPlanGating;

    protected static ?string $navigationLabel = 'Bakery Dashboard';

    protected static ?string $title = 'Bakery Dashboard';

    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    public function getWidgets(): array
    {
        return [
            // Row 1: Welcome banner (full width)
            WelcomeBannerWidget::class,
            // Row 2: Stats overview (full width)
            StatsOverview::class,
            // Row 3: Revenue chart (2/3) + Order funnel (1/3)
            RevenueChartWidget::class,
            OrderFunnelWidget::class,
            // Row 4: Recent orders (1/2) + Upcoming orders (1/2)
            RecentOrdersWidget::class,
            UpcomingOrdersWidget::class,
            // Row 5: Top products (1/3) + Customer insights (1/3) + Quick actions (1/3)
            TopProductsWidget::class,
            CustomerInsightsWidget::class,
            QuickActionsWidget::class,
            // Row 6: At-risk customers (1/2) + Low stock (1/2)
            AtRiskCustomersWidget::class,
            LowStockWidget::class,
            // Row 7: Storefront views (full width)
            StorefrontViewsWidget::class,
        ];
    }
}
