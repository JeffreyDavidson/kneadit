<?php

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Pages\Analytics\FinanceSummary;
use App\Filament\Pages\Analytics\ProductTrends;
use App\Filament\Pages\Analytics\ProfitAnalysis;
use App\Filament\Pages\Analytics\ReportsCenter;
use App\Filament\Pages\Analytics\StorefrontAnalytics;
use App\Filament\Pages\Analytics\SurveyResults;
use App\Filament\Pages\Dashboard\Dashboard;
use App\Filament\Pages\Dashboard\DashboardConfig;
use App\Filament\Pages\Engagement\AnnouncementBanner;
use App\Filament\Pages\Engagement\LoyaltyDashboard;
use App\Filament\Pages\Engagement\SocialCalendar;
use App\Filament\Pages\Operations\BakingSheet;
use App\Filament\Pages\Operations\CustomerDirectory;
use App\Filament\Pages\Operations\DeliveryRoutePlanner;
use App\Filament\Pages\Operations\HolidayPlanningCalendar;
use App\Filament\Pages\Operations\OrderCalendar;
use App\Filament\Pages\Operations\ScheduleManager;
use App\Filament\Pages\Operations\SeasonalItems;
use App\Filament\Pages\Operations\WeeklyPrepPlanner;
use App\Filament\Pages\Platform\ActivityLogPage;
use App\Filament\Pages\Platform\HelpCenter;
use App\Filament\Pages\Platform\UpgradePlan;
use App\Filament\Pages\Settings\HomepageBuilder;
use App\Filament\Pages\Settings\ManagePageContent;
use App\Filament\Pages\Settings\ManageSettings;
use App\Filament\Pages\Settings\ThemeSelector;
use App\Filament\Pages\Tools\DescriptionGenerator;
use App\Filament\Pages\Tools\LabelGenerator;
use App\Filament\Pages\Tools\PriceSuggestionTool;
use App\Filament\Pages\Tools\PricingEngine;
use App\Filament\Pages\Tools\RecipeCostCalculator;
use App\Filament\Pages\Tools\ShoppingListGenerator;
use App\Filament\Pages\Tools\SmartShoppingList;
use App\Models\Platform\Tenant;
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

    $tenant = new Tenant;
    $tenant->forceFill([
        'id' => 'admin-page-test',
        'plan' => SubscriptionTier::Starter,
    ]);
    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize($tenant);
});

dataset('adminPageGroups', [
    'Dashboard pages' => [
        Dashboard::class,
        DashboardConfig::class,
    ],
    'Settings pages' => [
        ManageSettings::class,
        HomepageBuilder::class,
        ManagePageContent::class,
        ThemeSelector::class,
    ],
    'Platform pages' => [
        HelpCenter::class,
        UpgradePlan::class,
        ActivityLogPage::class,
    ],
    'Operations pages' => [
        OrderCalendar::class,
        CustomerDirectory::class,
        ScheduleManager::class,
        BakingSheet::class,
        DeliveryRoutePlanner::class,
        HolidayPlanningCalendar::class,
        SeasonalItems::class,
        WeeklyPrepPlanner::class,
    ],
    'Analytics pages' => [
        FinanceSummary::class,
        ReportsCenter::class,
        ProductTrends::class,
        ProfitAnalysis::class,
        StorefrontAnalytics::class,
        SurveyResults::class,
    ],
    'Engagement pages' => [
        LoyaltyDashboard::class,
        AnnouncementBanner::class,
        SocialCalendar::class,
    ],
    'Tools pages' => [
        LabelGenerator::class,
        DescriptionGenerator::class,
        PriceSuggestionTool::class,
        PricingEngine::class,
        RecipeCostCalculator::class,
        ShoppingListGenerator::class,
        SmartShoppingList::class,
    ],
]);

test('admin pages can render', function (string ...$pageClasses) {
    foreach ($pageClasses as $pageClass) {
        Livewire::test($pageClass)
            ->assertOk();
    }
})->with('adminPageGroups');
