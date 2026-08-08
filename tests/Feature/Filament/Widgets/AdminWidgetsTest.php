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

dataset('adminWidgets', [
    'StatsOverview' => [App\Filament\Widgets\StatsOverview::class],
    'RecentOrdersWidget' => [App\Filament\Widgets\RecentOrdersWidget::class],
    'QuickActionsWidget' => [App\Filament\Widgets\QuickActionsWidget::class],
    'WelcomeBannerWidget' => [App\Filament\Widgets\WelcomeBannerWidget::class],
    'TodaysOrdersWidget' => [App\Filament\Widgets\TodaysOrdersWidget::class],
    'LowStockWidget' => [App\Filament\Widgets\LowStockWidget::class],
    'TopProductsWidget' => [App\Filament\Widgets\TopProductsWidget::class],
    'CustomerInsightsWidget' => [App\Filament\Widgets\CustomerInsightsWidget::class],
    'RecentActivityWidget' => [App\Filament\Widgets\RecentActivityWidget::class],
    'UpcomingOrdersWidget' => [App\Filament\Widgets\UpcomingOrdersWidget::class],
    'BirthdayWidget' => [App\Filament\Widgets\BirthdayWidget::class],
    'CapacityTodayWidget' => [App\Filament\Widgets\CapacityTodayWidget::class],
    'AtRiskCustomersWidget' => [App\Filament\Widgets\AtRiskCustomersWidget::class],
    'GiftCardBalanceWidget' => [App\Filament\Widgets\GiftCardBalanceWidget::class],
    'GoalTrackerWidget' => [App\Filament\Widgets\GoalTrackerWidget::class],
    'LoyaltyLeadersWidget' => [App\Filament\Widgets\LoyaltyLeadersWidget::class],
    'OrderFunnelWidget' => [App\Filament\Widgets\OrderFunnelWidget::class],
    'ReviewSummaryWidget' => [App\Filament\Widgets\ReviewSummaryWidget::class],
    'SeasonalItemsWidget' => [App\Filament\Widgets\SeasonalItemsWidget::class],
    'StorefrontViewsWidget' => [App\Filament\Widgets\StorefrontViewsWidget::class],
    'UpcomingHolidayWidget' => [App\Filament\Widgets\UpcomingHolidayWidget::class],
    'WeeklyRevenueChart' => [App\Filament\Widgets\WeeklyRevenueChart::class],
    'BakingSheetWidget' => [App\Filament\Widgets\BakingSheetWidget::class],
    'CateringPipelineWidget' => [App\Filament\Widgets\CateringPipelineWidget::class],
    'CouponUsageWidget' => [App\Filament\Widgets\CouponUsageWidget::class],
    'InboxWidget' => [App\Filament\Widgets\InboxWidget::class],
    'MarginAlertWidget' => [App\Filament\Widgets\MarginAlertWidget::class],
    'ReorderRemindersWidget' => [App\Filament\Widgets\ReorderRemindersWidget::class],
    'RevenueChartWidget' => [App\Filament\Widgets\RevenueChartWidget::class],
]);

test('admin widget can render', function (string $widgetClass) {
    Livewire::test($widgetClass)
        ->assertOk();
})->with('adminWidgets');
