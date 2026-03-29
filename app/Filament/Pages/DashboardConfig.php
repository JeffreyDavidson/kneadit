<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class DashboardConfig extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Dashboard Configuration';

    protected static ?string $title = 'Customize Dashboard';

    protected static ?string $slug = 'dashboard-config';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.dashboard-config';

    /** @var array<int, mixed> */
    public array $widgets = [];

    public bool $showPreview = false;

    /** @var array<string, mixed> */
    protected array $widgetMeta = [
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

    public function mount(): void
    {
        $this->loadWidgets();
    }

    protected function loadWidgets(): void
    {
        $saved = settings('dashboard_widgets');
        $config = $saved ? json_decode($saved, true) : null;

        if (! $config) {
            $config = $this->getDefaults();
        }

        uasort($config, fn (array $a, array $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

        $this->widgets = [];
        foreach ($config as $key => $settings) {
            if (! isset($this->widgetMeta[$key])) {
                continue;
            }
            $this->widgets[] = [
                'key' => $key,
                'visible' => $settings['visible'] ?? true,
                'span' => $settings['span'] ?? $this->widgetMeta[$key]['defaultSpan'] ?? 1,
                'name' => $this->widgetMeta[$key]['name'],
                'description' => $this->widgetMeta[$key]['description'],
                'icon' => $this->widgetMeta[$key]['icon'],
            ];
        }

        // Add any missing widgets
        foreach ($this->widgetMeta as $key => $meta) {
            if (! collect($this->widgets)->where('key', $key)->count()) {
                $this->widgets[] = [
                    'key' => $key,
                    'visible' => true,
                    'span' => $meta['defaultSpan'] ?? 1,
                    'name' => $meta['name'],
                    'description' => $meta['description'],
                    'icon' => $meta['icon'],
                ];
            }
        }
    }

    public function reorder(int $oldIndex, int $newIndex): void
    {
        $item = $this->widgets[$oldIndex];
        array_splice($this->widgets, $oldIndex, 1);
        array_splice($this->widgets, $newIndex, 0, [$item]);
        // widgets is already a list
    }

    public function togglePreview(): void
    {
        $this->showPreview = ! $this->showPreview;
    }

    public function setSpan(int $index, int $span): void
    {
        $this->widgets[$index]['span'] = max(1, min(3, $span));
    }

    public function toggleWidget(int $index): void
    {
        $this->widgets[$index]['visible'] = ! $this->widgets[$index]['visible'];
    }

    public function save(): void
    {
        $config = [];
        foreach ($this->widgets as $i => $widget) {
            $config[$widget['key']] = [
                'visible' => $widget['visible'],
                'order' => $i + 1,
                'span' => $widget['span'] ?? 1,
            ];
        }

        settings(['dashboard_widgets' => json_encode($config)]);

        Notification::make()
            ->title('Dashboard layout saved!')
            ->body('Your dashboard will reflect these changes immediately.')
            ->success()
            ->send();
    }

    public function resetDefaults(): void
    {
        settings(['dashboard_widgets' => json_encode($this->getDefaults())]);
        settings(['dashboard_grid_layout' => null]);
        $this->loadWidgets();

        Notification::make()
            ->title('Dashboard reset to defaults')
            ->success()
            ->send();
    }

    /** @return array<string, mixed> */
    protected function getDefaults(): array
    {
        $defaults = [];
        $i = 1;
        foreach ($this->widgetMeta as $key => $meta) {
            $defaults[$key] = ['visible' => true, 'order' => $i++];
        }

        return $defaults;
    }
}
