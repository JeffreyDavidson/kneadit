<?php

namespace App\Filament\Pages\Dashboard;

use App\Enums\Filament\WidgetSize;
use App\Filament\Shared\Dashboard\WidgetMeta;
use App\Filament\Widgets\AtRiskCustomersWidget;
use App\Filament\Widgets\BakingSheetWidget;
use App\Filament\Widgets\BirthdayWidget;
use App\Filament\Widgets\CapacityTodayWidget;
use App\Filament\Widgets\CateringPipelineWidget;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Filament\Widgets\CouponUsageWidget;
use App\Filament\Widgets\CustomerInsightsWidget;
use App\Filament\Widgets\GiftCardBalanceWidget;
use App\Filament\Widgets\GoalTrackerWidget;
use App\Filament\Widgets\InboxWidget;
use App\Filament\Widgets\LowStockWidget;
use App\Filament\Widgets\LoyaltyLeadersWidget;
use App\Filament\Widgets\MarginAlertWidget;
use App\Filament\Widgets\NeedsAttentionWidget;
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
use App\Services\Settings\SettingsManager;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    public function getHeading(): string|Htmlable
    {
        return 'Dashboard';
    }

    /** @return array<string, class-string<Widget>> */
    protected function getWidgetRegistry(): array
    {
        return [
            'welcome_banner' => WelcomeBannerWidget::class,
            'stats_overview' => StatsOverview::class,
            'order_funnel' => OrderFunnelWidget::class,
            'capacity_today' => CapacityTodayWidget::class,
            'revenue_chart' => RevenueChartWidget::class,
            'weekly_revenue' => WeeklyRevenueChart::class,
            'needs_attention' => NeedsAttentionWidget::class,
            'top_products' => TopProductsWidget::class,
            'customer_insights' => CustomerInsightsWidget::class,
            'storefront_views' => StorefrontViewsWidget::class,
            'recent_orders' => RecentOrdersWidget::class,
            'todays_orders' => TodaysOrdersWidget::class,
            'upcoming_orders' => UpcomingOrdersWidget::class,
            'at_risk_customers' => AtRiskCustomersWidget::class,
            'low_stock' => LowStockWidget::class,
            'baking_sheet' => BakingSheetWidget::class,
            'birthday' => BirthdayWidget::class,
            'inbox' => InboxWidget::class,
            'margin_alert' => MarginAlertWidget::class,
            'goal_tracker' => GoalTrackerWidget::class,
            'upcoming_holiday' => UpcomingHolidayWidget::class,
            'coupon_usage' => CouponUsageWidget::class,
            'gift_card_balance' => GiftCardBalanceWidget::class,
            'catering_pipeline' => CateringPipelineWidget::class,
            'review_summary' => ReviewSummaryWidget::class,
            'reorder_reminders' => ReorderRemindersWidget::class,
            'loyalty_leaders' => LoyaltyLeadersWidget::class,
            'seasonal_items' => SeasonalItemsWidget::class,
            // Audit log — bottom of the dashboard, always last.
            'recent_activity' => RecentActivityWidget::class,
        ];
    }

    /** @return array<class-string<Widget>|WidgetConfiguration> */
    public function getWidgets(): array
    {
        $registry = $this->getWidgetRegistry();

        $saved = resolve(SettingsManager::class)->get('dashboard_widgets');
        $config = $saved ? json_decode($saved, true) : null;

        // No saved layout yet → render every registered widget in default order
        // at its widget-meta default size. The DashboardConfig page
        // (Settings → Dashboard Configuration) is where bakers customize this.
        // Skip widgets flagged defaultHidden so reporting-flavoured tiles
        // don't crowd a fresh ops dashboard.
        if (! $config) {
            /** @var array<class-string<Widget>|WidgetConfiguration> */
            return collect($registry)
                ->reject(fn (string $class, string $key): bool => WidgetMeta::isDefaultHidden($key))
                ->map(fn (string $class, string $key) => $this->wrapWithSize($class, $key, null))
                ->values()
                ->all();
        }

        // Sort by saved order, drop hidden widgets, drop unknown keys.
        uasort($config, fn (array $a, array $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

        /** @var array<int, class-string<Widget>|WidgetConfiguration> $widgets */
        $widgets = [];
        foreach ($config as $key => $settings) {
            if (! ($settings['visible'] ?? true)) {
                continue;
            }
            if (! isset($registry[$key])) {
                continue;
            }
            $widgets[] = $this->wrapWithSize($registry[$key], $key, $settings['size'] ?? null);
        }

        // Surface registry entries that aren't yet in the saved layout —
        // happens when a new widget ships and existing tenants already
        // have a saved config. Show at default size so they're
        // discoverable; bakers can reposition or hide via the
        // Dashboard Configuration page. defaultHidden widgets stay
        // out so reporting-flavoured tiles don't auto-appear for
        // existing tenants either.
        foreach ($registry as $key => $class) {
            if (array_key_exists($key, $config)) {
                continue;
            }
            if (WidgetMeta::isDefaultHidden($key)) {
                continue;
            }
            $widgets[] = $this->wrapWithSize($class, $key, null);
        }

        return $widgets;
    }

    /**
     * Wrap a widget class with its dashboardSize property pulled from
     * saved settings (or the widget meta's default if absent / invalid).
     * Widgets that don't use the HasDashboardSize trait pass through
     * untouched.
     *
     * @param class-string<Widget> $class
     * @return class-string<Widget>|WidgetConfiguration
     */
    private function wrapWithSize(string $class, string $key, ?string $savedSize): string|WidgetConfiguration
    {
        if (! in_array(HasDashboardSize::class, class_uses_recursive($class), true)) {
            return $class;
        }

        $allowed = WidgetMeta::allowedSizesFor($key);
        $resolved = WidgetSize::tryFrom((string) $savedSize);

        if ($resolved === null || ! in_array($resolved, $allowed, true)) {
            $meta = WidgetMeta::get($key);
            $resolved = $meta['defaultSize'] ?? WidgetSize::Small;
        }

        return $class::make(['dashboardSize' => $resolved->value]);
    }
}
