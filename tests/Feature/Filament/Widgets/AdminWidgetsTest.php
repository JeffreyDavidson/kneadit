<?php

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
use App\Filament\Widgets\QuickActionsWidget;
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
use App\Filament\Widgets\WeeklyRevenueChartWidget;
use App\Filament\Widgets\WelcomeBannerWidget;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

dataset('adminWidgetGroups', [
    'Core widgets' => [
        StatsOverview::class,
        QuickActionsWidget::class,
        WelcomeBannerWidget::class,
    ],
    'Revenue widgets' => [
        WeeklyRevenueChartWidget::class,
        MarginAlertWidget::class,
        RevenueChartWidget::class,
    ],
    'Order widgets' => [
        RecentOrdersWidget::class,
        TodaysOrdersWidget::class,
        UpcomingOrdersWidget::class,
        OrderFunnelWidget::class,
    ],
    'Product and prep widgets' => [
        LowStockWidget::class,
        TopProductsWidget::class,
        BakingSheetWidget::class,
    ],
    'Customer widgets' => [
        CustomerInsightsWidget::class,
        BirthdayWidget::class,
        AtRiskCustomersWidget::class,
    ],
    'Communication widgets' => [
        RecentActivityWidget::class,
        InboxWidget::class,
    ],
    'Planning widgets' => [
        GoalTrackerWidget::class,
        UpcomingHolidayWidget::class,
        StorefrontViewsWidget::class,
    ],
    'Promotion and loyalty widgets' => [
        GiftCardBalanceWidget::class,
        LoyaltyLeadersWidget::class,
        CouponUsageWidget::class,
    ],
    'Operations widgets' => [
        CapacityTodayWidget::class,
        SeasonalItemsWidget::class,
        CateringPipelineWidget::class,
    ],
    'Feedback widgets' => [
        ReviewSummaryWidget::class,
        ReorderRemindersWidget::class,
    ],
]);

test('admin widgets can render', function (string ...$widgetClasses) {
    foreach ($widgetClasses as $widgetClass) {
        Livewire::test($widgetClass)
            ->assertOk();
    }
})->with('adminWidgetGroups');
