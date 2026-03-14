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

    protected array $widgetMeta = [
        'welcome_banner' => ['name' => 'Welcome Banner', 'description' => 'Greeting with quick stats', 'icon' => '👋', 'w' => 12, 'h' => 2, 'minW' => 6, 'minH' => 2],
        'stats_overview' => ['name' => 'Stats Overview', 'description' => 'Key metrics at a glance', 'icon' => '📊', 'w' => 12, 'h' => 2, 'minW' => 6, 'minH' => 2],
        'revenue_chart' => ['name' => 'Revenue Chart', 'description' => 'Revenue trends over time', 'icon' => '📈', 'w' => 8, 'h' => 4, 'minW' => 4, 'minH' => 3],
        'recent_orders' => ['name' => 'Recent Orders', 'description' => 'Latest orders with status', 'icon' => '🧾', 'w' => 4, 'h' => 4, 'minW' => 3, 'minH' => 3],
        'upcoming_orders' => ['name' => 'Upcoming Orders', 'description' => 'Orders due soon', 'icon' => '📅', 'w' => 4, 'h' => 4, 'minW' => 3, 'minH' => 3],
        'top_products' => ['name' => 'Top Products', 'description' => 'Best-selling items', 'icon' => '⭐', 'w' => 4, 'h' => 4, 'minW' => 3, 'minH' => 3],
        'customer_insights' => ['name' => 'Customer Insights', 'description' => 'Customer trends', 'icon' => '👥', 'w' => 4, 'h' => 4, 'minW' => 3, 'minH' => 3],
        'at_risk_customers' => ['name' => 'At-Risk Customers', 'description' => 'Inactive customers', 'icon' => '⚠️', 'w' => 8, 'h' => 4, 'minW' => 4, 'minH' => 3],
        'low_stock' => ['name' => 'Low Stock Alerts', 'description' => 'Ingredients running low', 'icon' => '📦', 'w' => 4, 'h' => 3, 'minW' => 3, 'minH' => 2],
        'birthday' => ['name' => 'Birthday Reminders', 'description' => 'Upcoming birthdays', 'icon' => '🎂', 'w' => 4, 'h' => 3, 'minW' => 3, 'minH' => 2],
        'recent_activity' => ['name' => 'Recent Activity', 'description' => 'Latest actions', 'icon' => '🕐', 'w' => 8, 'h' => 3, 'minW' => 4, 'minH' => 2],
    ];

    public function mount(): void
    {
        $this->loadWidgets();
    }

    protected function loadWidgets(): void
    {
        $savedGrid = Setting::get('dashboard_grid_layout');
        $gridLayout = $savedGrid ? json_decode($savedGrid, true) : [];

        $savedVisibility = Setting::get('dashboard_widgets');
        $visConfig = $savedVisibility ? json_decode($savedVisibility, true) : null;

        $this->widgets = [];
        $y = 0;
        foreach ($this->widgetMeta as $key => $meta) {
            $visible = true;
            if ($visConfig && isset($visConfig[$key])) {
                $visible = $visConfig[$key]['visible'] ?? true;
            }

            $grid = $gridLayout[$key] ?? null;

            $this->widgets[] = [
                'key' => $key,
                'name' => $meta['name'],
                'description' => $meta['description'],
                'icon' => $meta['icon'],
                'visible' => $visible,
                'x' => $grid['x'] ?? 0,
                'y' => $grid['y'] ?? $y,
                'w' => $grid['w'] ?? $meta['w'],
                'h' => $grid['h'] ?? $meta['h'],
                'minW' => $meta['minW'],
                'minH' => $meta['minH'],
            ];
            $y += ($grid['h'] ?? $meta['h']);
        }
    }

    public function toggleWidget(string $key): void
    {
        foreach ($this->widgets as &$widget) {
            if ($widget['key'] === $key) {
                $widget['visible'] = ! $widget['visible'];
                break;
            }
        }
    }

    public function saveLayout(array $gridData): void
    {
        $gridLayout = [];
        $visConfig = [];

        foreach ($gridData as $item) {
            $key = $item['id'] ?? null;
            if (! $key || ! isset($this->widgetMeta[$key])) {
                continue;
            }
            $gridLayout[$key] = [
                'x' => (int) ($item['x'] ?? 0),
                'y' => (int) ($item['y'] ?? 0),
                'w' => (int) ($item['w'] ?? 4),
                'h' => (int) ($item['h'] ?? 3),
            ];
        }

        // Save visibility from widgets array
        foreach ($this->widgets as $widget) {
            $visConfig[$widget['key']] = [
                'visible' => $widget['visible'],
                'order' => $gridLayout[$widget['key']]['y'] ?? 99,
            ];
        }

        Setting::set('dashboard_grid_layout', json_encode($gridLayout));
        Setting::set('dashboard_widgets', json_encode($visConfig));

        Notification::make()
            ->title('Dashboard layout saved!')
            ->body('Your dashboard will reflect these changes immediately.')
            ->success()
            ->send();
    }

    public function resetDefaults(): void
    {
        Setting::set('dashboard_grid_layout', null);
        Setting::set('dashboard_widgets', null);
        $this->loadWidgets();

        Notification::make()
            ->title('Dashboard reset to defaults')
            ->success()
            ->send();
    }
}
