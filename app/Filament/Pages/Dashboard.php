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

    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    public function getHeading(): string|Htmlable
    {
        $configUrl = route('filament.admin.pages.dashboard-config');

        return 'Bakery Dashboard';
    }

    protected function getWidgetRegistry(): array
    {
        return [
            'welcome_banner' => WelcomeBannerWidget::class,
            'stats_overview' => StatsOverview::class,
            'revenue_chart' => RevenueChartWidget::class,
            'order_funnel' => OrderFunnelWidget::class,
            'recent_orders' => RecentOrdersWidget::class,
            'upcoming_orders' => UpcomingOrdersWidget::class,
            'top_products' => TopProductsWidget::class,
            'customer_insights' => CustomerInsightsWidget::class,
            'quick_actions' => QuickActionsWidget::class,
            'at_risk_customers' => AtRiskCustomersWidget::class,
            'low_stock' => LowStockWidget::class,
            'storefront_views' => StorefrontViewsWidget::class,
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
