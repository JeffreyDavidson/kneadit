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

dataset('adminPages', [
    'Dashboard' => [App\Filament\Pages\Dashboard\Dashboard::class],
    'ManageSettings' => [App\Filament\Pages\Settings\ManageSettings::class],
    'HelpCenter' => [App\Filament\Pages\Platform\HelpCenter::class],
    'OrderCalendar' => [App\Filament\Pages\Operations\OrderCalendar::class],
    'FinanceSummary' => [App\Filament\Pages\Analytics\FinanceSummary::class],
    'ReportsCenter' => [App\Filament\Pages\Analytics\ReportsCenter::class],
    'LoyaltyDashboard' => [App\Filament\Pages\Engagement\LoyaltyDashboard::class],
    'CustomerDirectory' => [App\Filament\Pages\Operations\CustomerDirectory::class],
    'ScheduleManager' => [App\Filament\Pages\Operations\ScheduleManager::class],
    'BakingSheet' => [App\Filament\Pages\Operations\BakingSheet::class],
    'DeliveryRoutePlanner' => [App\Filament\Pages\Operations\DeliveryRoutePlanner::class],
    'LabelGenerator' => [App\Filament\Pages\Tools\LabelGenerator::class],
    'UpgradePlan' => [App\Filament\Pages\Platform\UpgradePlan::class],
]);

test('admin page can render', function (string $pageClass) {
    Livewire::test($pageClass)
        ->assertOk();
})->with('adminPages');

dataset('moreAdminPages', [
    'ActivityLogPage' => [App\Filament\Pages\Platform\ActivityLogPage::class],
    'AnnouncementBanner' => [App\Filament\Pages\Engagement\AnnouncementBanner::class],
    'DashboardConfig' => [App\Filament\Pages\Dashboard\DashboardConfig::class],
    'DescriptionGenerator' => [App\Filament\Pages\Tools\DescriptionGenerator::class],
    'HolidayPlanningCalendar' => [App\Filament\Pages\Operations\HolidayPlanningCalendar::class],
    'HomepageBuilder' => [App\Filament\Pages\Settings\HomepageBuilder::class],
    'ManagePageContent' => [App\Filament\Pages\Settings\ManagePageContent::class],
    'PriceSuggestionTool' => [App\Filament\Pages\Tools\PriceSuggestionTool::class],
    'PricingEngine' => [App\Filament\Pages\Tools\PricingEngine::class],
    'ProductTrends' => [App\Filament\Pages\Analytics\ProductTrends::class],
    'ProfitAnalysis' => [App\Filament\Pages\Analytics\ProfitAnalysis::class],
    'RecipeCostCalculator' => [App\Filament\Pages\Tools\RecipeCostCalculator::class],
    'SeasonalItems' => [App\Filament\Pages\Operations\SeasonalItems::class],
    'ShoppingListGenerator' => [App\Filament\Pages\Tools\ShoppingListGenerator::class],
    'SmartShoppingList' => [App\Filament\Pages\Tools\SmartShoppingList::class],
    'SocialCalendar' => [App\Filament\Pages\Engagement\SocialCalendar::class],
    'StorefrontAnalytics' => [App\Filament\Pages\Analytics\StorefrontAnalytics::class],
    'SurveyResults' => [App\Filament\Pages\Analytics\SurveyResults::class],
    'ThemeSelector' => [App\Filament\Pages\Settings\ThemeSelector::class],
    'WeeklyPrepPlanner' => [App\Filament\Pages\Operations\WeeklyPrepPlanner::class],
]);

test('additional admin page can render', function (string $pageClass) {
    Livewire::test($pageClass)
        ->assertOk();
})->with('moreAdminPages');
