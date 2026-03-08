<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class DashboardConfig extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Dashboard Configuration';

    protected static ?string $title = 'Customize Dashboard';

    protected static ?string $slug = 'dashboard-config';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.dashboard-config';

    public array $widgets = [];

    protected array $widgetMeta = [
        'welcome_banner' => ['name' => 'Welcome Banner', 'description' => 'Greeting message with quick stats', 'icon' => '👋'],
        'stats_overview' => ['name' => 'Stats Overview', 'description' => 'Key metrics at a glance — orders, revenue, customers', 'icon' => '📊'],
        'revenue_chart' => ['name' => 'Revenue Chart', 'description' => 'Visual revenue trends over time', 'icon' => '📈'],
        'order_funnel' => ['name' => 'Order Funnel', 'description' => 'Order status breakdown', 'icon' => '🔽'],
        'recent_orders' => ['name' => 'Recent Orders', 'description' => 'Latest orders with status', 'icon' => '🧾'],
        'upcoming_orders' => ['name' => 'Upcoming Orders', 'description' => 'Orders due soon', 'icon' => '📅'],
        'top_products' => ['name' => 'Top Products', 'description' => 'Best-selling items', 'icon' => '⭐'],
        'customer_insights' => ['name' => 'Customer Insights', 'description' => 'Customer trends and segments', 'icon' => '👥'],
        'quick_actions' => ['name' => 'Quick Actions', 'description' => 'Shortcuts to common tasks', 'icon' => '⚡'],
        'at_risk_customers' => ['name' => 'At-Risk Customers', 'description' => 'Customers who haven\'t ordered recently', 'icon' => '⚠️'],
        'low_stock' => ['name' => 'Low Stock Alerts', 'description' => 'Ingredients running low', 'icon' => '📦'],
        'storefront_views' => ['name' => 'Storefront Views', 'description' => 'Online store traffic stats', 'icon' => '🏪'],
        'birthday' => ['name' => 'Birthday Reminders', 'description' => 'Upcoming customer birthdays', 'icon' => '🎂'],
        'recent_activity' => ['name' => 'Recent Activity', 'description' => 'Latest actions and events', 'icon' => '🕐'],
    ];

    public function mount(): void
    {
        $saved = Setting::get('dashboard_widgets');
        $config = $saved ? json_decode($saved, true) : null;

        if (! $config) {
            $config = $this->getDefaults();
        }

        // Sort by order
        uasort($config, fn ($a, $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

        $this->widgets = [];
        foreach ($config as $key => $settings) {
            if (! isset($this->widgetMeta[$key])) {
                continue;
            }
            $this->widgets[] = [
                'key' => $key,
                'visible' => $settings['visible'] ?? true,
                'order' => $settings['order'] ?? 99,
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
                    'order' => count($this->widgets) + 1,
                    'name' => $meta['name'],
                    'description' => $meta['description'],
                    'icon' => $meta['icon'],
                ];
            }
        }
    }

    public function moveUp(int $index): void
    {
        if ($index <= 0) return;
        $temp = $this->widgets[$index];
        $this->widgets[$index] = $this->widgets[$index - 1];
        $this->widgets[$index - 1] = $temp;
        $this->reindex();
    }

    public function moveDown(int $index): void
    {
        if ($index >= count($this->widgets) - 1) return;
        $temp = $this->widgets[$index];
        $this->widgets[$index] = $this->widgets[$index + 1];
        $this->widgets[$index + 1] = $temp;
        $this->reindex();
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
            ];
        }

        Setting::set('dashboard_widgets', json_encode($config));

        Notification::make()
            ->title('Dashboard layout saved!')
            ->body('Your dashboard will reflect these changes immediately.')
            ->success()
            ->send();
    }

    public function resetDefaults(): void
    {
        Setting::set('dashboard_widgets', json_encode($this->getDefaults()));
        $this->mount();

        Notification::make()
            ->title('Dashboard reset to defaults')
            ->success()
            ->send();
    }

    protected function reindex(): void
    {
        foreach ($this->widgets as $i => &$widget) {
            $widget['order'] = $i + 1;
        }
    }

    protected function getDefaults(): array
    {
        return [
            'welcome_banner' => ['visible' => true, 'order' => 1],
            'stats_overview' => ['visible' => true, 'order' => 2],
            'revenue_chart' => ['visible' => true, 'order' => 3],
            'order_funnel' => ['visible' => true, 'order' => 4],
            'recent_orders' => ['visible' => true, 'order' => 5],
            'upcoming_orders' => ['visible' => true, 'order' => 6],
            'top_products' => ['visible' => true, 'order' => 7],
            'customer_insights' => ['visible' => true, 'order' => 8],
            'quick_actions' => ['visible' => true, 'order' => 9],
            'at_risk_customers' => ['visible' => true, 'order' => 10],
            'low_stock' => ['visible' => true, 'order' => 11],
            'storefront_views' => ['visible' => true, 'order' => 12],
            'birthday' => ['visible' => true, 'order' => 13],
            'recent_activity' => ['visible' => true, 'order' => 14],
        ];
    }
}
