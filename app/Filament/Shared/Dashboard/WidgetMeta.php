<?php

namespace App\Filament\Shared\Dashboard;

use App\Enums\Filament\WidgetSize;
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
        // Core — full-width heroes, single allowed size each.
        'welcome_banner' => ['class' => WelcomeBannerWidget::class, 'name' => 'Welcome Banner', 'description' => 'Greeting with quick stats and actions', 'icon' => '👋', 'defaultSize' => WidgetSize::Large, 'allowedSizes' => [WidgetSize::Large, WidgetSize::ExtraLarge]],
        'stats_overview' => ['class' => StatsOverview::class, 'name' => 'Stats Overview', 'description' => 'Key metrics — orders, revenue, customers', 'icon' => '📊', 'defaultSize' => WidgetSize::Large, 'allowedSizes' => [WidgetSize::Large, WidgetSize::ExtraLarge]],

        // Revenue & Finance — charts need horizontal room (XL adds vertical breakdown).
        'revenue_chart' => ['class' => RevenueChartWidget::class, 'name' => 'Revenue Chart', 'description' => 'Monthly revenue trends', 'icon' => '📈', 'defaultSize' => WidgetSize::Medium, 'allowedSizes' => [WidgetSize::Medium, WidgetSize::Large, WidgetSize::ExtraLarge]],
        'weekly_revenue' => ['class' => WeeklyRevenueChart::class, 'name' => 'Weekly Revenue', 'description' => 'This week\'s revenue breakdown', 'icon' => '💰', 'defaultSize' => WidgetSize::Medium, 'allowedSizes' => [WidgetSize::Medium, WidgetSize::Large, WidgetSize::ExtraLarge]],
        'margin_alert' => ['class' => MarginAlertWidget::class, 'name' => 'Margin Alerts', 'description' => 'Products with low profit margins', 'icon' => '💸', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small, WidgetSize::Medium]],

        // Orders — recent_orders + upcoming_orders are flexible tables; today's needs a wider row.
        'order_funnel' => ['class' => OrderFunnelWidget::class, 'name' => 'Order Funnel', 'description' => 'Order status breakdown', 'icon' => '🔽', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small, WidgetSize::Medium]],
        'recent_orders' => ['class' => RecentOrdersWidget::class, 'name' => 'Recent Orders', 'description' => 'Latest orders with status', 'icon' => '🧾', 'defaultSize' => WidgetSize::Small],
        'todays_orders' => ['class' => TodaysOrdersWidget::class, 'name' => 'Today\'s Orders', 'description' => 'All orders for today', 'icon' => '📋', 'defaultSize' => WidgetSize::Medium, 'allowedSizes' => [WidgetSize::Medium, WidgetSize::Large]],
        'upcoming_orders' => ['class' => UpcomingOrdersWidget::class, 'name' => 'Upcoming Orders', 'description' => 'Orders due in next 3 days', 'icon' => '📅', 'defaultSize' => WidgetSize::Small],

        // Products & Prep
        'top_products' => ['class' => TopProductsWidget::class, 'name' => 'Top Products', 'description' => 'Best-selling items this month', 'icon' => '⭐', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small, WidgetSize::Medium]],
        'baking_sheet' => ['class' => BakingSheetWidget::class, 'name' => 'Baking Sheet', 'description' => 'Today\'s baking prep list', 'icon' => '🧁', 'defaultSize' => WidgetSize::Medium, 'allowedSizes' => [WidgetSize::Medium, WidgetSize::Large]],
        'low_stock' => ['class' => LowStockWidget::class, 'name' => 'Low Stock Alerts', 'description' => 'Ingredients running low', 'icon' => '📦', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small, WidgetSize::Medium]],

        // Customers
        'customer_insights' => ['class' => CustomerInsightsWidget::class, 'name' => 'Customer Insights', 'description' => 'Customer trends and segments', 'icon' => '👥', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small, WidgetSize::Medium]],
        'at_risk_customers' => ['class' => AtRiskCustomersWidget::class, 'name' => 'At-Risk Customers', 'description' => 'Inactive customers needing attention', 'icon' => '⚠️', 'defaultSize' => WidgetSize::Medium, 'allowedSizes' => [WidgetSize::Medium, WidgetSize::Large]],
        'birthday' => ['class' => BirthdayWidget::class, 'name' => 'Birthday Reminders', 'description' => 'Upcoming customer birthdays', 'icon' => '🎂', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small, WidgetSize::Medium]],

        // Communication & Activity
        'inbox' => ['class' => InboxWidget::class, 'name' => 'Inbox', 'description' => 'Unread customer messages', 'icon' => '📬', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small, WidgetSize::Medium]],
        'recent_activity' => ['class' => RecentActivityWidget::class, 'name' => 'Recent Activity', 'description' => 'Latest actions and events', 'icon' => '🕐', 'defaultSize' => WidgetSize::Medium, 'allowedSizes' => [WidgetSize::Medium, WidgetSize::Large]],

        // Planning
        'goal_tracker' => ['class' => GoalTrackerWidget::class, 'name' => 'Goal Tracker', 'description' => 'Business goal progress', 'icon' => '🎯', 'defaultSize' => WidgetSize::Medium, 'allowedSizes' => [WidgetSize::Medium, WidgetSize::Large]],
        'upcoming_holiday' => ['class' => UpcomingHolidayWidget::class, 'name' => 'Upcoming Holiday', 'description' => 'Next holiday prep reminder', 'icon' => '🎄', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small]],
        'storefront_views' => ['class' => StorefrontViewsWidget::class, 'name' => 'Storefront Views', 'description' => 'Online store traffic today', 'icon' => '🏪', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small]],

        // Promotions & Loyalty
        'coupon_usage' => ['class' => CouponUsageWidget::class, 'name' => 'Coupon Usage', 'description' => 'Active coupons and redemption stats', 'icon' => '🎫', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small, WidgetSize::Medium]],
        'gift_card_balance' => ['class' => GiftCardBalanceWidget::class, 'name' => 'Gift Card Balance', 'description' => 'Outstanding gift card liability', 'icon' => '🎁', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small]],
        'loyalty_leaders' => ['class' => LoyaltyLeadersWidget::class, 'name' => 'Loyalty Leaders', 'description' => 'Top customers by loyalty points', 'icon' => '🏅', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small, WidgetSize::Medium]],

        // Operations
        'capacity_today' => ['class' => CapacityTodayWidget::class, 'name' => 'Capacity Today', 'description' => 'Today and tomorrow order capacity', 'icon' => '⏰', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small, WidgetSize::Medium]],
        'catering_pipeline' => ['class' => CateringPipelineWidget::class, 'name' => 'Catering Pipeline', 'description' => 'Open catering inquiries and quotes', 'icon' => '📝', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small, WidgetSize::Medium]],
        'seasonal_items' => ['class' => SeasonalItemsWidget::class, 'name' => 'Seasonal Items', 'description' => 'Products going in/out of season', 'icon' => '🌸', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small, WidgetSize::Medium]],

        // Feedback
        'review_summary' => ['class' => ReviewSummaryWidget::class, 'name' => 'Review Summary', 'description' => 'Average rating and recent reviews', 'icon' => '⭐', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small, WidgetSize::Medium]],
        'reorder_reminders' => ['class' => ReorderRemindersWidget::class, 'name' => 'Reorder Reminders', 'description' => 'Lapsed repeat customers', 'icon' => '🔄', 'defaultSize' => WidgetSize::Small, 'allowedSizes' => [WidgetSize::Small]],
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

    /**
     * Sizes the bakery owner is allowed to choose for this widget.
     * Defaults to all sizes when the meta entry doesn't constrain
     * (treats absence as "fully flexible").
     *
     * @return list<WidgetSize>
     */
    public static function allowedSizesFor(string $key): array
    {
        $allowed = self::WIDGETS[$key]['allowedSizes'] ?? null;

        return is_array($allowed) && $allowed !== [] ? $allowed : WidgetSize::standardSizes();
    }
}
