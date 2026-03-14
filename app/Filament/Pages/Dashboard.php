<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AtRiskCustomersWidget;
use App\Filament\Widgets\BirthdayWidget;
use App\Filament\Widgets\CustomerInsightsWidget;
use App\Filament\Widgets\LowStockWidget;
use App\Filament\Widgets\OrderFunnelWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\StorefrontViewsWidget;
use App\Filament\Widgets\TopProductsWidget;
use App\Filament\Widgets\UpcomingOrdersWidget;
use App\Filament\Widgets\WelcomeBannerWidget;
use App\Models\Setting;
use App\Traits\HasPlanGating;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    use HasPlanGating;

    protected static ?string $navigationLabel = 'Bakery Dashboard';

    protected static ?string $title = 'Bakery Dashboard';

    protected string $view = 'filament.pages.dashboard';

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

    /**
     * Default grid positions for each widget (12-column grid).
     */
    protected function getDefaultGridLayout(): array
    {
        return [
            'welcome_banner'    => ['x' => 0, 'y' => 0,  'w' => 12, 'h' => 2, 'minW' => 6, 'minH' => 2],
            'stats_overview'    => ['x' => 0, 'y' => 2,  'w' => 12, 'h' => 3, 'minW' => 6, 'minH' => 2],
            'revenue_chart'     => ['x' => 0, 'y' => 5,  'w' => 8,  'h' => 4, 'minW' => 4, 'minH' => 3],
            'recent_orders'     => ['x' => 8, 'y' => 5,  'w' => 4,  'h' => 4, 'minW' => 3, 'minH' => 3],
            'upcoming_orders'   => ['x' => 0, 'y' => 9,  'w' => 4,  'h' => 4, 'minW' => 3, 'minH' => 3],
            'top_products'      => ['x' => 4, 'y' => 9,  'w' => 4,  'h' => 4, 'minW' => 3, 'minH' => 3],
            'customer_insights' => ['x' => 8, 'y' => 9,  'w' => 4,  'h' => 4, 'minW' => 3, 'minH' => 3],
            'at_risk_customers' => ['x' => 0, 'y' => 13, 'w' => 8,  'h' => 4, 'minW' => 4, 'minH' => 3],
            'low_stock'         => ['x' => 8, 'y' => 13, 'w' => 4,  'h' => 4, 'minW' => 3, 'minH' => 3],
            'birthday'          => ['x' => 0, 'y' => 17, 'w' => 4,  'h' => 3, 'minW' => 3, 'minH' => 2],
            'recent_activity'   => ['x' => 4, 'y' => 17, 'w' => 8,  'h' => 3, 'minW' => 4, 'minH' => 2],
        ];
    }

    /**
     * Get widgets with their grid positions for the Blade view.
     */
    public function getWidgetsWithGrid(): array
    {
        $registry = $this->getWidgetRegistry();
        $defaults = $this->getDefaultGridLayout();

        // Load saved grid layout
        $savedLayout = Setting::get('dashboard_grid_layout');
        $gridLayout = $savedLayout ? json_decode($savedLayout, true) : [];

        // Load visibility config
        $savedWidgets = Setting::get('dashboard_widgets');
        $widgetConfig = $savedWidgets ? json_decode($savedWidgets, true) : null;

        $result = [];
        foreach ($registry as $key => $class) {
            // Check visibility
            if ($widgetConfig && isset($widgetConfig[$key]) && ! ($widgetConfig[$key]['visible'] ?? true)) {
                continue;
            }

            $grid = $gridLayout[$key] ?? $defaults[$key] ?? ['x' => 0, 'y' => 99, 'w' => 4, 'h' => 3];

            $result[] = [
                'key' => $key,
                'class' => $class,
                'x' => $grid['x'] ?? 0,
                'y' => $grid['y'] ?? 0,
                'w' => $grid['w'] ?? 4,
                'h' => $grid['h'] ?? 3,
                'minW' => $grid['minW'] ?? $defaults[$key]['minW'] ?? 2,
                'minH' => $grid['minH'] ?? $defaults[$key]['minH'] ?? 2,
            ];
        }

        return $result;
    }

    /**
     * Save grid layout positions from JS.
     */
    public function saveGridLayout(array $layout): void
    {
        Setting::set('dashboard_grid_layout', json_encode($layout));
    }

    public function getGridConfig(): array
    {
        return $this->getDefaultGridLayout();
    }

    protected function getWidgetRegistry(): array
    {
        return [
            'welcome_banner' => WelcomeBannerWidget::class,
            'stats_overview' => StatsOverview::class,
            'revenue_chart' => RevenueChartWidget::class,
            // 'order_funnel' => OrderFunnelWidget::class,
            'recent_orders' => RecentOrdersWidget::class,
            'upcoming_orders' => UpcomingOrdersWidget::class,
            'top_products' => TopProductsWidget::class,
            'customer_insights' => CustomerInsightsWidget::class,
            // 'quick_actions' => QuickActionsWidget::class,
            'at_risk_customers' => AtRiskCustomersWidget::class,
            'low_stock' => LowStockWidget::class,
            // 'storefront_views' => StorefrontViewsWidget::class,
            'birthday' => BirthdayWidget::class,
            'recent_activity' => RecentActivityWidget::class,
        ];
    }

    public function getWidgets(): array
    {
        $registry = $this->getWidgetRegistry();

        $saved = Setting::get('dashboard_widgets');
        $config = $saved ? json_decode($saved, true) : null;

        if (! $config) {
            // Fallback: return all widgets in default order
            return array_values($registry);
        }

        // Sort by order
        uasort($config, fn ($a, $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

        $widgets = [];
        foreach ($config as $key => $settings) {
            if (! ($settings['visible'] ?? true)) {
                continue;
            }
            if (isset($registry[$key])) {
                $widgets[] = $registry[$key];
            }
        }

        // Add any registry widgets not in config (new widgets)
        foreach ($registry as $key => $class) {
            if (! isset($config[$key]) && ! in_array($class, $widgets)) {
                $widgets[] = $class;
            }
        }

        return $widgets;
    }
}
