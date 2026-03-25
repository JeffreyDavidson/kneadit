<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AtRiskCustomersWidget;
use App\Filament\Widgets\BakingSheetWidget;
use App\Filament\Widgets\BirthdayWidget;
use App\Filament\Widgets\CapacityTodayWidget;
use App\Filament\Widgets\CateringPipelineWidget;
use App\Filament\Widgets\CouponUsageWidget;
use App\Filament\Widgets\CustomerInsightsWidget;
use App\Filament\Widgets\GiftCardBalanceWidget;
use App\Filament\Widgets\GoalTrackerWidget;
use App\Filament\Widgets\InboxWidget;
use App\Filament\Widgets\LowStockWidget;
use App\Filament\Widgets\LoyaltyLeadersWidget;
use App\Filament\Widgets\MarginAlertWidget;
use App\Filament\Widgets\OrderFunnelWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\ReorderRemindersWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\ReviewSummaryWidget;
use App\Filament\Widgets\SeasonalItemsWidget;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\StorefrontViewsWidget;
use App\Filament\Widgets\TodaysOrdersWidget;
use App\Filament\Widgets\TopProductsWidget;
use App\Filament\Widgets\UpcomingHolidayWidget;
use App\Filament\Widgets\UpcomingOrdersWidget;
use App\Filament\Widgets\WeeklyRevenueChart;
use App\Filament\Widgets\WelcomeBannerWidget;
use App\Models\Setting;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Bakery Dashboard';

    protected static ?string $title = 'Bakery Dashboard';

    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    public function getHeading(): string|Htmlable
    {
        return 'Bakery Dashboard';
    }

    protected function getWidgetRegistry(): array
    {
        return [
            'welcome_banner' => WelcomeBannerWidget::class,
            'stats_overview' => StatsOverview::class,
            'revenue_chart' => RevenueChartWidget::class,
            'weekly_revenue' => WeeklyRevenueChart::class,
            'order_funnel' => OrderFunnelWidget::class,
            'recent_orders' => RecentOrdersWidget::class,
            'todays_orders' => TodaysOrdersWidget::class,
            'upcoming_orders' => UpcomingOrdersWidget::class,
            'top_products' => TopProductsWidget::class,
            'customer_insights' => CustomerInsightsWidget::class,
            'at_risk_customers' => AtRiskCustomersWidget::class,
            'low_stock' => LowStockWidget::class,
            'baking_sheet' => BakingSheetWidget::class,
            'birthday' => BirthdayWidget::class,
            'recent_activity' => RecentActivityWidget::class,
            'inbox' => InboxWidget::class,
            'margin_alert' => MarginAlertWidget::class,
            'goal_tracker' => GoalTrackerWidget::class,
            'upcoming_holiday' => UpcomingHolidayWidget::class,
            'storefront_views' => StorefrontViewsWidget::class,
            'coupon_usage' => CouponUsageWidget::class,
            'gift_card_balance' => GiftCardBalanceWidget::class,
            'capacity_today' => CapacityTodayWidget::class,
            'catering_pipeline' => CateringPipelineWidget::class,
            'review_summary' => ReviewSummaryWidget::class,
            'reorder_reminders' => ReorderRemindersWidget::class,
            'loyalty_leaders' => LoyaltyLeadersWidget::class,
            'seasonal_items' => SeasonalItemsWidget::class,
        ];
    }

    public function getWidgets(): array
    {
        $registry = $this->getWidgetRegistry();

        // Dashboard customization disabled until config page is finalized
        // When ready, set 'dashboard_config_enabled' to 'true' in Settings
        $enabled = Setting::get('dashboard_config_enabled') === 'true';

        if (! $enabled) {
            return array_values($registry);
        }

        $saved = Setting::get('dashboard_widgets');
        $config = $saved ? json_decode($saved, true) : null;

        if (! $config) {
            return array_values($registry);
        }

        // Sort by order
        uasort($config, fn (array $a, array $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

        $widgets = [];
        foreach ($config as $key => $settings) {
            if (! ($settings['visible'] ?? true)) {
                continue;
            }
            if (isset($registry[$key])) {
                $widgets[] = $registry[$key];
            }
        }

        return $widgets;
    }
}
