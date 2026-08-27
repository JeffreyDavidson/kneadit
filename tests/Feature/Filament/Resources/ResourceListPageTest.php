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

// BlogPosts and EmailCampaigns use the central connection and are tested separately.
dataset('resourceListPageGroups', [
    'Scheduling resources' => [
        App\Filament\Resources\BlockedDates\Pages\ListBlockedDates::class,
        App\Filament\Resources\CapacityLimits\Pages\ListCapacityLimits::class,
        App\Filament\Resources\Holidays\Pages\ListHolidays::class,
    ],
    'Catalog resources' => [
        App\Filament\Resources\Categories\Pages\ListCategories::class,
        App\Filament\Resources\Ingredients\Pages\ListIngredients::class,
        App\Filament\Resources\Products\Pages\ListProducts::class,
        App\Filament\Resources\Recipes\Pages\ListRecipes::class,
        App\Filament\Resources\Suppliers\Pages\ListSuppliers::class,
    ],
    'Customer resources' => [
        App\Filament\Resources\CateringInquiries\Pages\ListCateringInquiries::class,
        App\Filament\Resources\ContactMessages\Pages\ListContactMessages::class,
        App\Filament\Resources\CustomerPhotos\Pages\ListCustomerPhotos::class,
        App\Filament\Resources\Customers\Pages\ListCustomers::class,
        App\Filament\Resources\Reviews\Pages\ListReviews::class,
        App\Filament\Resources\Surveys\Pages\ListSurveys::class,
        App\Filament\Resources\WaitlistEntries\Pages\ListWaitlistEntries::class,
    ],
    'Promotion resources' => [
        App\Filament\Resources\Coupons\Pages\ListCoupons::class,
        App\Filament\Resources\GiftCards\Pages\ListGiftCards::class,
        App\Filament\Resources\LoyaltyRewards\Pages\ListLoyaltyRewards::class,
    ],
    'Commerce resources' => [
        App\Filament\Resources\Expenses\Pages\ListExpenses::class,
        App\Filament\Resources\Incomes\Pages\ListIncomes::class,
        App\Filament\Resources\Orders\Pages\ListOrders::class,
    ],
    'Content resources' => [
        App\Filament\Resources\GalleryPhotos\Pages\ListGalleryPhotos::class,
        App\Filament\Resources\Settings\Pages\ListSettings::class,
        App\Filament\Resources\SocialPosts\Pages\ListSocialPosts::class,
    ],
]);

test('resource list pages can render', function (string ...$pageClasses) {
    foreach ($pageClasses as $pageClass) {
        Livewire::test($pageClass)
            ->assertOk();
    }
})->with('resourceListPageGroups');
