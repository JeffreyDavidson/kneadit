<?php

namespace App\Filament\Shared\Dashboard;

class WidgetMeta
{
    /** @var array<string, array<string, mixed>> */
    public const array WIDGETS = [
        // Core
        'welcome_banner' => ['name' => 'Welcome Banner', 'description' => 'Greeting with quick stats and actions', 'icon' => '👋', 'defaultSpan' => 3],
        'stats_overview' => ['name' => 'Stats Overview', 'description' => 'Key metrics — orders, revenue, customers', 'icon' => '📊', 'defaultSpan' => 3],

        // Revenue & Finance
        'revenue_chart' => ['name' => 'Revenue Chart', 'description' => 'Monthly revenue trends', 'icon' => '📈', 'defaultSpan' => 2],
        'weekly_revenue' => ['name' => 'Weekly Revenue', 'description' => 'This week\'s revenue breakdown', 'icon' => '💰', 'defaultSpan' => 2],
        'margin_alert' => ['name' => 'Margin Alerts', 'description' => 'Products with low profit margins', 'icon' => '💸', 'defaultSpan' => 1],

        // Orders
        'order_funnel' => ['name' => 'Order Funnel', 'description' => 'Order status breakdown', 'icon' => '🔽', 'defaultSpan' => 1],
        'recent_orders' => ['name' => 'Recent Orders', 'description' => 'Latest orders with status', 'icon' => '🧾', 'defaultSpan' => 1],
        'todays_orders' => ['name' => 'Today\'s Orders', 'description' => 'All orders for today', 'icon' => '📋', 'defaultSpan' => 2],
        'upcoming_orders' => ['name' => 'Upcoming Orders', 'description' => 'Orders due in next 3 days', 'icon' => '📅', 'defaultSpan' => 1],

        // Products & Prep
        'top_products' => ['name' => 'Top Products', 'description' => 'Best-selling items this month', 'icon' => '⭐', 'defaultSpan' => 1],
        'baking_sheet' => ['name' => 'Baking Sheet', 'description' => 'Today\'s baking prep list', 'icon' => '🧁', 'defaultSpan' => 2],
        'low_stock' => ['name' => 'Low Stock Alerts', 'description' => 'Ingredients running low', 'icon' => '📦', 'defaultSpan' => 1],

        // Customers
        'customer_insights' => ['name' => 'Customer Insights', 'description' => 'Customer trends and segments', 'icon' => '👥', 'defaultSpan' => 1],
        'at_risk_customers' => ['name' => 'At-Risk Customers', 'description' => 'Inactive customers needing attention', 'icon' => '⚠️', 'defaultSpan' => 2],
        'birthday' => ['name' => 'Birthday Reminders', 'description' => 'Upcoming customer birthdays', 'icon' => '🎂', 'defaultSpan' => 1],

        // Communication & Activity
        'inbox' => ['name' => 'Inbox', 'description' => 'Unread customer messages', 'icon' => '📬', 'defaultSpan' => 1],
        'recent_activity' => ['name' => 'Recent Activity', 'description' => 'Latest actions and events', 'icon' => '🕐', 'defaultSpan' => 2],

        // Planning
        'goal_tracker' => ['name' => 'Goal Tracker', 'description' => 'Business goal progress', 'icon' => '🎯', 'defaultSpan' => 2],
        'upcoming_holiday' => ['name' => 'Upcoming Holiday', 'description' => 'Next holiday prep reminder', 'icon' => '🎄', 'defaultSpan' => 1],
        'storefront_views' => ['name' => 'Storefront Views', 'description' => 'Online store traffic today', 'icon' => '🏪', 'defaultSpan' => 1],

        // Promotions & Loyalty
        'coupon_usage' => ['name' => 'Coupon Usage', 'description' => 'Active coupons and redemption stats', 'icon' => '🎫', 'defaultSpan' => 1],
        'gift_card_balance' => ['name' => 'Gift Card Balance', 'description' => 'Outstanding gift card liability', 'icon' => '🎁', 'defaultSpan' => 1],
        'loyalty_leaders' => ['name' => 'Loyalty Leaders', 'description' => 'Top customers by loyalty points', 'icon' => '🏅', 'defaultSpan' => 1],

        // Operations
        'capacity_today' => ['name' => 'Capacity Today', 'description' => 'Today and tomorrow order capacity', 'icon' => '⏰', 'defaultSpan' => 1],
        'catering_pipeline' => ['name' => 'Catering Pipeline', 'description' => 'Open catering inquiries and quotes', 'icon' => '📝', 'defaultSpan' => 1],
        'seasonal_items' => ['name' => 'Seasonal Items', 'description' => 'Products going in/out of season', 'icon' => '🌸', 'defaultSpan' => 1],

        // Feedback
        'review_summary' => ['name' => 'Review Summary', 'description' => 'Average rating and recent reviews', 'icon' => '⭐', 'defaultSpan' => 1],
        'reorder_reminders' => ['name' => 'Reorder Reminders', 'description' => 'Lapsed repeat customers', 'icon' => '🔄', 'defaultSpan' => 1],
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
}
