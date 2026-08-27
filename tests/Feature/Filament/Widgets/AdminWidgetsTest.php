<?php

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
        App\Filament\Widgets\StatsOverview::class,
        App\Filament\Widgets\QuickActionsWidget::class,
        App\Filament\Widgets\WelcomeBannerWidget::class,
    ],
    'Revenue widgets' => [
        App\Filament\Widgets\WeeklyRevenueChartWidget::class,
        App\Filament\Widgets\MarginAlertWidget::class,
        App\Filament\Widgets\RevenueChartWidget::class,
    ],
    'Order widgets' => [
        App\Filament\Widgets\RecentOrdersWidget::class,
        App\Filament\Widgets\TodaysOrdersWidget::class,
        App\Filament\Widgets\UpcomingOrdersWidget::class,
        App\Filament\Widgets\OrderFunnelWidget::class,
    ],
    'Product and prep widgets' => [
        App\Filament\Widgets\LowStockWidget::class,
        App\Filament\Widgets\TopProductsWidget::class,
        App\Filament\Widgets\BakingSheetWidget::class,
    ],
    'Customer widgets' => [
        App\Filament\Widgets\CustomerInsightsWidget::class,
        App\Filament\Widgets\BirthdayWidget::class,
        App\Filament\Widgets\AtRiskCustomersWidget::class,
    ],
    'Communication widgets' => [
        App\Filament\Widgets\RecentActivityWidget::class,
        App\Filament\Widgets\InboxWidget::class,
    ],
    'Planning widgets' => [
        App\Filament\Widgets\GoalTrackerWidget::class,
        App\Filament\Widgets\UpcomingHolidayWidget::class,
        App\Filament\Widgets\StorefrontViewsWidget::class,
    ],
    'Promotion and loyalty widgets' => [
        App\Filament\Widgets\GiftCardBalanceWidget::class,
        App\Filament\Widgets\LoyaltyLeadersWidget::class,
        App\Filament\Widgets\CouponUsageWidget::class,
    ],
    'Operations widgets' => [
        App\Filament\Widgets\CapacityTodayWidget::class,
        App\Filament\Widgets\SeasonalItemsWidget::class,
        App\Filament\Widgets\CateringPipelineWidget::class,
    ],
    'Feedback widgets' => [
        App\Filament\Widgets\ReviewSummaryWidget::class,
        App\Filament\Widgets\ReorderRemindersWidget::class,
    ],
]);

test('admin widgets can render', function (string ...$widgetClasses) {
    foreach ($widgetClasses as $widgetClass) {
        Livewire::test($widgetClass)
            ->assertOk();
    }
})->with('adminWidgetGroups');
