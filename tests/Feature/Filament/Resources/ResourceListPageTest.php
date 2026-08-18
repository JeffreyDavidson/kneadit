<?php

use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

dataset('resourceListPages', [
    'BlockedDates' => [App\Filament\Resources\BlockedDates\Pages\ListBlockedDates::class],
    // BlogPosts excluded — uses central connection, tested separately
    'CapacityLimits' => [App\Filament\Resources\CapacityLimits\Pages\ListCapacityLimits::class],
    'Categories' => [App\Filament\Resources\Categories\Pages\ListCategories::class],
    'CateringInquiries' => [App\Filament\Resources\CateringInquiries\Pages\ListCateringInquiries::class],
    'ContactMessages' => [App\Filament\Resources\ContactMessages\Pages\ListContactMessages::class],
    'Coupons' => [App\Filament\Resources\Coupons\Pages\ListCoupons::class],
    'CustomerPhotos' => [App\Filament\Resources\CustomerPhotos\Pages\ListCustomerPhotos::class],
    'Customers' => [App\Filament\Resources\Customers\Pages\ListCustomers::class],
    // EmailCampaigns excluded — uses central connection, tested separately
    'Expenses' => [App\Filament\Resources\Expenses\Pages\ListExpenses::class],
    'GalleryPhotos' => [App\Filament\Resources\GalleryPhotos\Pages\ListGalleryPhotos::class],
    'GiftCards' => [App\Filament\Resources\GiftCards\Pages\ListGiftCards::class],
    'Holidays' => [App\Filament\Resources\Holidays\Pages\ListHolidays::class],
    'Incomes' => [App\Filament\Resources\Incomes\Pages\ListIncomes::class],
    'Ingredients' => [App\Filament\Resources\Ingredients\Pages\ListIngredients::class],
    'LoyaltyRewards' => [App\Filament\Resources\LoyaltyRewards\Pages\ListLoyaltyRewards::class],
    'Orders' => [App\Filament\Resources\Orders\Pages\ListOrders::class],
    'Products' => [App\Filament\Resources\Products\Pages\ListProducts::class],
    'Recipes' => [App\Filament\Resources\Recipes\Pages\ListRecipes::class],
    'Reviews' => [App\Filament\Resources\Reviews\Pages\ListReviews::class],
    'Settings' => [App\Filament\Resources\Settings\Pages\ListSettings::class],
    'SocialPosts' => [App\Filament\Resources\SocialPosts\Pages\ListSocialPosts::class],
    'Suppliers' => [App\Filament\Resources\Suppliers\Pages\ListSuppliers::class],
    'Surveys' => [App\Filament\Resources\Surveys\Pages\ListSurveys::class],
    'WaitlistEntries' => [App\Filament\Resources\WaitlistEntries\Pages\ListWaitlistEntries::class],
]);

test('resource list page can render', function (string $pageClass) {
    Livewire::test($pageClass)
        ->assertOk();
})->with('resourceListPages');
