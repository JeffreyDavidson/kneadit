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

        return new \Illuminate\Support\HtmlString(
            '<div class="flex items-center justify-between w-full">'
            .'<span>Bakery Dashboard</span>'
            .'<a href="'.$configUrl.'" style="color: #9ca3af;" title="Customize Dashboard">'
            .'<svg xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px;" viewBox="0 0 20 20" fill="currentColor">'
            .'<path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />'
            .'</svg>'
            .'</a>'
            .'</div>'
        );
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
