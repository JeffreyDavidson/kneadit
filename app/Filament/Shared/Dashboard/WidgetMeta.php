<?php

namespace App\Filament\Shared\Dashboard;

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

class WidgetMeta
{
    /** @var array<string, array<string, mixed>> */
    public const array WIDGETS = [
        // Core
        'welcome_banner' => ['class' => WelcomeBannerWidget::class, 'name' => 'Welcome Banner', 'description' => 'Greeting with quick stats and actions', 'icon' => '👋', 'defaultSpan' => 3],
        'stats_overview' => ['class' => StatsOverview::class, 'name' => 'Stats Overview', 'description' => 'Key metrics — orders, revenue, customers', 'icon' => '📊', 'defaultSpan' => 3],

        // Revenue & Finance
        'revenue_chart' => ['class' => RevenueChartWidget::class, 'name' => 'Revenue Chart', 'description' => 'Monthly revenue trends', 'icon' => '📈', 'defaultSpan' => 2],
        'weekly_revenue' => ['class' => WeeklyRevenueChart::class, 'name' => 'Weekly Revenue', 'description' => 'This week\'s revenue breakdown', 'icon' => '💰', 'defaultSpan' => 2],
        'margin_alert' => ['class' => MarginAlertWidget::class, 'name' => 'Margin Alerts', 'description' => 'Products with low profit margins', 'icon' => '💸', 'defaultSpan' => 1],

        // Orders
        'order_funnel' => ['class' => OrderFunnelWidget::class, 'name' => 'Order Funnel', 'description' => 'Order status breakdown', 'icon' => '🔽', 'defaultSpan' => 1],
        'recent_orders' => ['class' => RecentOrdersWidget::class, 'name' => 'Recent Orders', 'description' => 'Latest orders with status', 'icon' => '🧾', 'defaultSpan' => 1],
        'todays_orders' => ['class' => TodaysOrdersWidget::class, 'name' => 'Today\'s Orders', 'description' => 'All orders for today', 'icon' => '📋', 'defaultSpan' => 2],
        'upcoming_orders' => ['class' => UpcomingOrdersWidget::class, 'name' => 'Upcoming Orders', 'description' => 'Orders due in next 3 days', 'icon' => '📅', 'defaultSpan' => 1],

        // Products & Prep
        'top_products' => ['class' => TopProductsWidget::class, 'name' => 'Top Products', 'description' => 'Best-selling items this month', 'icon' => '⭐', 'defaultSpan' => 1],
        'baking_sheet' => ['class' => BakingSheetWidget::class, 'name' => 'Baking Sheet', 'description' => 'Today\'s baking prep list', 'icon' => '🧁', 'defaultSpan' => 2],
        'low_stock' => ['class' => LowStockWidget::class, 'name' => 'Low Stock Alerts', 'description' => 'Ingredients running low', 'icon' => '📦', 'defaultSpan' => 1],

        // Customers
        'customer_insights' => ['class' => CustomerInsightsWidget::class, 'name' => 'Customer Insights', 'description' => 'Customer trends and segments', 'icon' => '👥', 'defaultSpan' => 1],
        'at_risk_customers' => ['class' => AtRiskCustomersWidget::class, 'name' => 'At-Risk Customers', 'description' => 'Inactive customers needing attention', 'icon' => '⚠️', 'defaultSpan' => 2],
        'birthday' => ['class' => BirthdayWidget::class, 'name' => 'Birthday Reminders', 'description' => 'Upcoming customer birthdays', 'icon' => '🎂', 'defaultSpan' => 1],

        // Communication & Activity
        'inbox' => ['class' => InboxWidget::class, 'name' => 'Inbox', 'description' => 'Unread customer messages', 'icon' => '📬', 'defaultSpan' => 1],
        'recent_activity' => ['class' => RecentActivityWidget::class, 'name' => 'Recent Activity', 'description' => 'Latest actions and events', 'icon' => '🕐', 'defaultSpan' => 2],

        // Planning
        'goal_tracker' => ['class' => GoalTrackerWidget::class, 'name' => 'Goal Tracker', 'description' => 'Business goal progress', 'icon' => '🎯', 'defaultSpan' => 2],
        'upcoming_holiday' => ['class' => UpcomingHolidayWidget::class, 'name' => 'Upcoming Holiday', 'description' => 'Next holiday prep reminder', 'icon' => '🎄', 'defaultSpan' => 1],
        'storefront_views' => ['class' => StorefrontViewsWidget::class, 'name' => 'Storefront Views', 'description' => 'Online store traffic today', 'icon' => '🏪', 'defaultSpan' => 1],

        // Promotions & Loyalty
        'coupon_usage' => ['class' => CouponUsageWidget::class, 'name' => 'Coupon Usage', 'description' => 'Active coupons and redemption stats', 'icon' => '🎫', 'defaultSpan' => 1],
        'gift_card_balance' => ['class' => GiftCardBalanceWidget::class, 'name' => 'Gift Card Balance', 'description' => 'Outstanding gift card liability', 'icon' => '🎁', 'defaultSpan' => 1],
        'loyalty_leaders' => ['class' => LoyaltyLeadersWidget::class, 'name' => 'Loyalty Leaders', 'description' => 'Top customers by loyalty points', 'icon' => '🏅', 'defaultSpan' => 1],

        // Operations
        'capacity_today' => ['class' => CapacityTodayWidget::class, 'name' => 'Capacity Today', 'description' => 'Today and tomorrow order capacity', 'icon' => '⏰', 'defaultSpan' => 1],
        'catering_pipeline' => ['class' => CateringPipelineWidget::class, 'name' => 'Catering Pipeline', 'description' => 'Open catering inquiries and quotes', 'icon' => '📝', 'defaultSpan' => 1],
        'seasonal_items' => ['class' => SeasonalItemsWidget::class, 'name' => 'Seasonal Items', 'description' => 'Products going in/out of season', 'icon' => '🌸', 'defaultSpan' => 1],

        // Feedback
        'review_summary' => ['class' => ReviewSummaryWidget::class, 'name' => 'Review Summary', 'description' => 'Average rating and recent reviews', 'icon' => '⭐', 'defaultSpan' => 1],
        'reorder_reminders' => ['class' => ReorderRemindersWidget::class, 'name' => 'Reorder Reminders', 'description' => 'Lapsed repeat customers', 'icon' => '🔄', 'defaultSpan' => 1],
    ];

    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        return self::WIDGETS;
    }

    /** @return array<string, mixed>|null */
    public static function get(string $key): ?array
    {
        return self::WIDGETS[$key] ?? null;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::WIDGETS);
    }

    /**
     * Resolve a widget key to its concrete class FQN.
     *
     * @return class-string|null
     */
    public static function classFor(string $key): ?string
    {
        return self::WIDGETS[$key]['class'] ?? null;
    }
}
