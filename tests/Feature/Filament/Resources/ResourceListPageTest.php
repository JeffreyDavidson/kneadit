<?php

use App\Filament\Resources\BlockedDates\Pages\ListBlockedDates;
use App\Filament\Resources\CapacityLimits\Pages\ListCapacityLimits;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\CateringInquiries\Pages\ListCateringInquiries;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Resources\CustomerPhotos\Pages\ListCustomerPhotos;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Expenses\Pages\ListExpenses;
use App\Filament\Resources\GalleryPhotos\Pages\ListGalleryPhotos;
use App\Filament\Resources\GiftCards\Pages\ListGiftCards;
use App\Filament\Resources\Holidays\Pages\ListHolidays;
use App\Filament\Resources\Incomes\Pages\ListIncomes;
use App\Filament\Resources\Ingredients\Pages\ListIngredients;
use App\Filament\Resources\LoyaltyRewards\Pages\ListLoyaltyRewards;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Recipes\Pages\ListRecipes;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Filament\Resources\SocialPosts\Pages\ListSocialPosts;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\Surveys\Pages\ListSurveys;
use App\Filament\Resources\WaitlistEntries\Pages\ListWaitlistEntries;
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
        ListBlockedDates::class,
        ListCapacityLimits::class,
        ListHolidays::class,
    ],
    'Catalog resources' => [
        ListCategories::class,
        ListIngredients::class,
        ListProducts::class,
        ListRecipes::class,
        ListSuppliers::class,
    ],
    'Customer resources' => [
        ListCateringInquiries::class,
        ListContactMessages::class,
        ListCustomerPhotos::class,
        ListCustomers::class,
        ListReviews::class,
        ListSurveys::class,
        ListWaitlistEntries::class,
    ],
    'Promotion resources' => [
        ListCoupons::class,
        ListGiftCards::class,
        ListLoyaltyRewards::class,
    ],
    'Commerce resources' => [
        ListExpenses::class,
        ListIncomes::class,
        ListOrders::class,
    ],
    'Content resources' => [
        ListGalleryPhotos::class,
        ListSettings::class,
        ListSocialPosts::class,
    ],
]);

test('resource list pages can render', function (string ...$pageClasses) {
    foreach ($pageClasses as $pageClass) {
        Livewire::test($pageClass)
            ->assertOk();
    }
})->with('resourceListPageGroups');
