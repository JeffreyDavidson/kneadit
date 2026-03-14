<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DashboardConfig extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Dashboard Configuration';

    protected static ?string $title = 'Customize Dashboard';

    protected static ?string $slug = 'dashboard-config';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.dashboard-config';

    public array $widgets = [];

    public bool $showPreview = false;

    protected array $widgetMeta = [
        'welcome_banner' => ['name' => 'Welcome Banner', 'description' => 'Greeting with quick stats and actions', 'icon' => '👋'],
        'stats_overview' => ['name' => 'Stats Overview', 'description' => 'Key metrics — orders, revenue, customers', 'icon' => '📊'],
        'revenue_chart' => ['name' => 'Revenue Chart', 'description' => 'Revenue trends over time', 'icon' => '📈'],
        'recent_orders' => ['name' => 'Recent Orders', 'description' => 'Latest orders with status', 'icon' => '🧾'],
        'upcoming_orders' => ['name' => 'Upcoming Orders', 'description' => 'Orders due soon', 'icon' => '📅'],
        'top_products' => ['name' => 'Top Products', 'description' => 'Best-selling items this month', 'icon' => '⭐'],
        'customer_insights' => ['name' => 'Customer Insights', 'description' => 'Customer trends and segments', 'icon' => '👥'],
        'at_risk_customers' => ['name' => 'At-Risk Customers', 'description' => 'Inactive customers needing attention', 'icon' => '⚠️'],
        'low_stock' => ['name' => 'Low Stock Alerts', 'description' => 'Ingredients running low', 'icon' => '📦'],
        'birthday' => ['name' => 'Birthday Reminders', 'description' => 'Upcoming customer birthdays', 'icon' => '🎂'],
        'recent_activity' => ['name' => 'Recent Activity', 'description' => 'Latest actions and events', 'icon' => '🕐'],
    ];

    public function mount(): void
    {
        $this->loadWidgets();
    }

    protected function loadWidgets(): void
    {
        $saved = Setting::get('dashboard_widgets');
        $config = $saved ? json_decode($saved, true) : null;

        if (! $config) {
            $config = $this->getDefaults();
        }

        uasort($config, fn ($a, $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

        $this->widgets = [];
        foreach ($config as $key => $settings) {
            if (! isset($this->widgetMeta[$key])) {
                continue;
            }
            $this->widgets[] = [
                'key' => $key,
                'visible' => $settings['visible'] ?? true,
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
        $this->widgets = array_values($this->widgets);
    }

    public function togglePreview(): void
    {
        $this->showPreview = ! $this->showPreview;
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
        Setting::set('dashboard_grid_layout', null);
        $this->loadWidgets();

        Notification::make()
            ->title('Dashboard reset to defaults')
            ->success()
            ->send();
    }

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
