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
use Filament\Support\Icons\Heroicon;

class WidgetMeta
{
    /** @var array<string, WidgetDefinition>|null */
    private static ?array $widgets = null;

    /** @return array<string, WidgetDefinition> */
    public static function all(): array
    {
        return self::$widgets ??= [
            // Core — full-width heroes, single allowed size each.
            'welcome_banner' => new WidgetDefinition(class: WelcomeBannerWidget::class, name: 'Quick Actions', description: 'Quick action buttons (new order, view orders, messages)', icon: Heroicon::OutlinedBolt, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small, WidgetSize::Medium]),
            'stats_overview' => new WidgetDefinition(class: StatsOverview::class, name: 'Stats Overview', description: 'Key metrics — orders, revenue, customers', icon: Heroicon::OutlinedChartBar, defaultSize: WidgetSize::Large, allowedSizes: [WidgetSize::Large, WidgetSize::ExtraLarge]),

            // Revenue & Finance — charts need horizontal room (XL adds vertical breakdown).
            'revenue_chart' => new WidgetDefinition(class: RevenueChartWidget::class, name: 'Revenue Chart', description: 'Monthly revenue trends', icon: Heroicon::OutlinedArrowTrendingUp, defaultSize: WidgetSize::Medium, allowedSizes: [WidgetSize::Medium, WidgetSize::Large, WidgetSize::ExtraLarge]),
            'weekly_revenue' => new WidgetDefinition(class: WeeklyRevenueChart::class, name: 'Weekly Revenue', description: 'This week\'s revenue breakdown', icon: Heroicon::OutlinedCurrencyDollar, defaultSize: WidgetSize::Medium, allowedSizes: [WidgetSize::Medium, WidgetSize::Large, WidgetSize::ExtraLarge], defaultHidden: true),
            'margin_alert' => new WidgetDefinition(class: MarginAlertWidget::class, name: 'Margin Alerts', description: 'Products with low profit margins', icon: Heroicon::OutlinedBanknotes, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small, WidgetSize::Medium]),

            // Orders — recent_orders + upcoming_orders are flexible tables; today's needs a wider row.
            'order_funnel' => new WidgetDefinition(class: OrderFunnelWidget::class, name: 'Order Funnel', description: 'Order status breakdown', icon: Heroicon::OutlinedFunnel, defaultSize: WidgetSize::Medium, allowedSizes: [WidgetSize::Small, WidgetSize::Medium]),
            'recent_orders' => new WidgetDefinition(class: RecentOrdersWidget::class, name: 'Recent Orders', description: 'Latest orders with status', icon: Heroicon::OutlinedReceiptRefund, defaultSize: WidgetSize::Small),
            'todays_orders' => new WidgetDefinition(class: TodaysOrdersWidget::class, name: 'Today\'s Orders', description: 'All orders for today', icon: Heroicon::OutlinedClipboardDocumentList, defaultSize: WidgetSize::Medium, allowedSizes: [WidgetSize::Medium, WidgetSize::Large]),
            'upcoming_orders' => new WidgetDefinition(class: UpcomingOrdersWidget::class, name: 'Upcoming Orders', description: 'Orders due in next 3 days', icon: Heroicon::OutlinedCalendarDays, defaultSize: WidgetSize::Small),

            // Products & Prep
            'top_products' => new WidgetDefinition(class: TopProductsWidget::class, name: 'Top Products', description: 'Best-selling items this month', icon: Heroicon::OutlinedStar, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small, WidgetSize::Medium], defaultHidden: true),
            'baking_sheet' => new WidgetDefinition(class: BakingSheetWidget::class, name: 'Baking Sheet', description: 'Today\'s baking prep list', icon: Heroicon::OutlinedCake, defaultSize: WidgetSize::Medium, allowedSizes: [WidgetSize::Medium, WidgetSize::Large]),
            'low_stock' => new WidgetDefinition(class: LowStockWidget::class, name: 'Low Stock Alerts', description: 'Ingredients running low', icon: Heroicon::OutlinedCube, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small, WidgetSize::Medium]),

            // Customers
            'customer_insights' => new WidgetDefinition(class: CustomerInsightsWidget::class, name: 'Customer Insights', description: 'Customer trends and segments', icon: Heroicon::OutlinedUsers, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small, WidgetSize::Medium], defaultHidden: true),
            'at_risk_customers' => new WidgetDefinition(class: AtRiskCustomersWidget::class, name: 'At-Risk Customers', description: 'Inactive customers needing attention', icon: Heroicon::OutlinedExclamationTriangle, defaultSize: WidgetSize::Medium, allowedSizes: [WidgetSize::Medium, WidgetSize::Large]),
            'birthday' => new WidgetDefinition(class: BirthdayWidget::class, name: 'Birthday Reminders', description: 'Upcoming customer birthdays', icon: Heroicon::OutlinedCake, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small, WidgetSize::Medium]),

            // Communication & Activity
            'inbox' => new WidgetDefinition(class: InboxWidget::class, name: 'Inbox', description: 'Unread customer messages', icon: Heroicon::OutlinedInboxArrowDown, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small, WidgetSize::Medium]),
            'recent_activity' => new WidgetDefinition(class: RecentActivityWidget::class, name: 'Recent Audit Log', description: 'Latest actions and events', icon: Heroicon::OutlinedClock, defaultSize: WidgetSize::Medium, allowedSizes: [WidgetSize::Medium, WidgetSize::Large]),

            // Planning
            'goal_tracker' => new WidgetDefinition(class: GoalTrackerWidget::class, name: 'Goal Tracker', description: 'Business goal progress', icon: Heroicon::OutlinedFlag, defaultSize: WidgetSize::Medium, allowedSizes: [WidgetSize::Medium, WidgetSize::Large], defaultHidden: true),
            'upcoming_holiday' => new WidgetDefinition(class: UpcomingHolidayWidget::class, name: 'Upcoming Holiday', description: 'Next holiday prep reminder', icon: Heroicon::OutlinedCalendar, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small]),
            'storefront_views' => new WidgetDefinition(class: StorefrontViewsWidget::class, name: 'Storefront Views', description: 'Online store traffic today', icon: Heroicon::OutlinedBuildingStorefront, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small], defaultHidden: true),

            // Promotions & Loyalty
            'coupon_usage' => new WidgetDefinition(class: CouponUsageWidget::class, name: 'Coupon Usage', description: 'Active coupons and redemption stats', icon: Heroicon::OutlinedTicket, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small, WidgetSize::Medium], defaultHidden: true),
            'gift_card_balance' => new WidgetDefinition(class: GiftCardBalanceWidget::class, name: 'Gift Card Balance', description: 'Outstanding gift card liability', icon: Heroicon::OutlinedGift, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small], defaultHidden: true),
            'loyalty_leaders' => new WidgetDefinition(class: LoyaltyLeadersWidget::class, name: 'Loyalty Leaders', description: 'Top customers by loyalty points', icon: Heroicon::OutlinedTrophy, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small, WidgetSize::Medium], defaultHidden: true),

            // Operations
            'capacity_today' => new WidgetDefinition(class: CapacityTodayWidget::class, name: 'Capacity Today', description: 'Today and tomorrow order capacity', icon: Heroicon::OutlinedClock, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small, WidgetSize::Medium]),
            'catering_pipeline' => new WidgetDefinition(class: CateringPipelineWidget::class, name: 'Catering Pipeline', description: 'Open catering inquiries and quotes', icon: Heroicon::OutlinedDocumentText, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small, WidgetSize::Medium]),
            'seasonal_items' => new WidgetDefinition(class: SeasonalItemsWidget::class, name: 'Seasonal Items', description: 'Products going in/out of season', icon: Heroicon::OutlinedSparkles, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small, WidgetSize::Medium], defaultHidden: true),

            // Feedback
            'review_summary' => new WidgetDefinition(class: ReviewSummaryWidget::class, name: 'Review Summary', description: 'Average rating and recent reviews', icon: Heroicon::OutlinedStar, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small, WidgetSize::Medium], defaultHidden: true),
            'reorder_reminders' => new WidgetDefinition(class: ReorderRemindersWidget::class, name: 'Reorder Reminders', description: 'Lapsed repeat customers', icon: Heroicon::OutlinedArrowPath, defaultSize: WidgetSize::Small, allowedSizes: [WidgetSize::Small]),
        ];
    }

    public static function get(string $key): ?WidgetDefinition
    {
        return self::all()[$key] ?? null;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::all());
    }

    /**
     * Resolve a widget key to its concrete class FQN.
     *
     * @return class-string<\Filament\Widgets\Widget>|null
     */
    public static function classFor(string $key): ?string
    {
        return self::get($key)?->class;
    }

    /**
     * Whether the widget is hidden by default on a fresh dashboard.
     * Reporting-flavoured widgets (top sellers, customer trends)
     * default to hidden so the ops dashboard ships clean; bakers can
     * opt in via Dashboard Configuration.
     */
    public static function isDefaultHidden(string $key): bool
    {
        $widget = self::get($key);

        return $widget instanceof WidgetDefinition && $widget->defaultHidden;
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
        $widget = self::get($key);

        return $widget instanceof WidgetDefinition
            ? $widget->allowedSizes()
            : WidgetSize::standardSizes();
    }
}
