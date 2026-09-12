<?php

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
        'plan' => App\Enums\Platform\SubscriptionTier::Starter,
    ]);
    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize($tenant);
});

dataset('adminPageGroups', [
    'Dashboard pages' => [
        App\Filament\Pages\Dashboard\Dashboard::class,
        App\Filament\Pages\Dashboard\DashboardConfig::class,
    ],
    'Settings pages' => [
        App\Filament\Pages\Settings\ManageSettings::class,
        App\Filament\Pages\Settings\HomepageBuilder::class,
        App\Filament\Pages\Settings\ManagePageContent::class,
        App\Filament\Pages\Settings\ThemeSelector::class,
    ],
    'Platform pages' => [
        App\Filament\Pages\Platform\HelpCenter::class,
        App\Filament\Pages\Platform\UpgradePlan::class,
        App\Filament\Pages\Platform\ActivityLogPage::class,
    ],
    'Operations pages' => [
        App\Filament\Pages\Operations\OrderCalendar::class,
        App\Filament\Pages\Operations\CustomerDirectory::class,
        App\Filament\Pages\Operations\ScheduleManager::class,
        App\Filament\Pages\Operations\BakingSheet::class,
        App\Filament\Pages\Operations\DeliveryRoutePlanner::class,
        App\Filament\Pages\Operations\HolidayPlanningCalendar::class,
        App\Filament\Pages\Operations\SeasonalItems::class,
        App\Filament\Pages\Operations\WeeklyPrepPlanner::class,
    ],
    'Analytics pages' => [
        App\Filament\Pages\Analytics\FinanceSummary::class,
        App\Filament\Pages\Analytics\ReportsCenter::class,
        App\Filament\Pages\Analytics\ProductTrends::class,
        App\Filament\Pages\Analytics\ProfitAnalysis::class,
        App\Filament\Pages\Analytics\StorefrontAnalytics::class,
        App\Filament\Pages\Analytics\SurveyResults::class,
    ],
    'Engagement pages' => [
        App\Filament\Pages\Engagement\LoyaltyDashboard::class,
        App\Filament\Pages\Engagement\AnnouncementBanner::class,
        App\Filament\Pages\Engagement\SocialCalendar::class,
    ],
    'Tools pages' => [
        App\Filament\Pages\Tools\LabelGenerator::class,
        App\Filament\Pages\Tools\DescriptionGenerator::class,
        App\Filament\Pages\Tools\PriceSuggestionTool::class,
        App\Filament\Pages\Tools\PricingEngine::class,
        App\Filament\Pages\Tools\RecipeCostCalculator::class,
        App\Filament\Pages\Tools\ShoppingListGenerator::class,
        App\Filament\Pages\Tools\SmartShoppingList::class,
    ],
]);

test('admin pages can render', function (string ...$pageClasses) {
    foreach ($pageClasses as $pageClass) {
        Livewire::test($pageClass)
            ->assertOk();
    }
})->with('adminPageGroups');
