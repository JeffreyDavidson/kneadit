<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

dataset('adminPages', [
    'Dashboard' => [App\Filament\Pages\Dashboard::class],
    'ManageSettings' => [App\Filament\Pages\ManageSettings::class],
    'HelpCenter' => [App\Filament\Pages\HelpCenter::class],
    'OrderCalendar' => [App\Filament\Pages\OrderCalendar::class],
    'FinanceSummary' => [App\Filament\Pages\FinanceSummary::class],
    'ReportsCenter' => [App\Filament\Pages\ReportsCenter::class],
    'LoyaltyDashboard' => [App\Filament\Pages\LoyaltyDashboard::class],
    'CustomerDirectory' => [App\Filament\Pages\CustomerDirectory::class],
    'ScheduleManager' => [App\Filament\Pages\ScheduleManager::class],
    'BakingSheet' => [App\Filament\Pages\BakingSheet::class],
    'DeliveryRoutePlanner' => [App\Filament\Pages\DeliveryRoutePlanner::class],
    'LabelGenerator' => [App\Filament\Pages\LabelGenerator::class],
    'UpgradePlan' => [App\Filament\Pages\UpgradePlan::class],
]);

test('admin page can render', function (string $pageClass) {
    Livewire::test($pageClass)
        ->assertOk();
})->with('adminPages');

dataset('moreAdminPages', [
    'ActivityLogPage' => [App\Filament\Pages\ActivityLogPage::class],
    'AnnouncementBanner' => [App\Filament\Pages\AnnouncementBanner::class],
    'DashboardConfig' => [App\Filament\Pages\DashboardConfig::class],
    'DescriptionGenerator' => [App\Filament\Pages\DescriptionGenerator::class],
    'HolidayPlanningCalendar' => [App\Filament\Pages\HolidayPlanningCalendar::class],
    'HomepageBuilder' => [App\Filament\Pages\HomepageBuilder::class],
    'ManagePageContent' => [App\Filament\Pages\ManagePageContent::class],
    'PriceSuggestionTool' => [App\Filament\Pages\PriceSuggestionTool::class],
    'PricingEngine' => [App\Filament\Pages\PricingEngine::class],
    'ProductTrends' => [App\Filament\Pages\ProductTrends::class],
    'ProfitAnalysis' => [App\Filament\Pages\ProfitAnalysis::class],
    'RecipeCostCalculator' => [App\Filament\Pages\RecipeCostCalculator::class],
    'SeasonalItems' => [App\Filament\Pages\SeasonalItems::class],
    'ShoppingListGenerator' => [App\Filament\Pages\ShoppingListGenerator::class],
    'SmartShoppingList' => [App\Filament\Pages\SmartShoppingList::class],
    'SocialCalendar' => [App\Filament\Pages\SocialCalendar::class],
    'StorefrontAnalytics' => [App\Filament\Pages\StorefrontAnalytics::class],
    'SurveyResults' => [App\Filament\Pages\SurveyResults::class],
    'ThemeSelector' => [App\Filament\Pages\ThemeSelector::class],
    'WeeklyPrepPlanner' => [App\Filament\Pages\WeeklyPrepPlanner::class],
]);

test('additional admin page can render', function (string $pageClass) {
    Livewire::test($pageClass)
        ->assertOk();
})->with('moreAdminPages');
