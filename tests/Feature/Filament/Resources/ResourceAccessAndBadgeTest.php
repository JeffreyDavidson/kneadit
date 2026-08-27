<?php

use App\Filament\Resources\Coupons\CouponResource;
use App\Filament\Resources\EmailCampaigns\EmailCampaignResource;
use App\Filament\Resources\GiftCards\GiftCardResource;
use App\Filament\Resources\Ingredients\IngredientResource;
use App\Filament\Resources\LoyaltyRewards\LoyaltyRewardResource;
use App\Filament\Resources\Recipes\RecipeResource;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Filament\Resources\SocialPosts\SocialPostResource;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

// -- canAccess tests for pro-feature-gated resources --

dataset('proFeatureResources', [
    'pro resources' => [
        EmailCampaignResource::class,
        LoyaltyRewardResource::class,
        IngredientResource::class,
        SocialPostResource::class,
    ],
]);

test('pro-feature resources can be accessed when feature is active', function (string ...$resourceClasses) {
    Feature::define('pro-features', fn () => true);

    foreach ($resourceClasses as $resourceClass) {
        expect($resourceClass::canAccess())->toBeTrue();
    }
})->with('proFeatureResources');

test('pro-feature resources cannot be accessed when feature is inactive', function (string ...$resourceClasses) {
    Feature::define('pro-features', fn () => false);

    foreach ($resourceClasses as $resourceClass) {
        expect($resourceClass::canAccess())->toBeFalse();
    }
})->with('proFeatureResources');

// -- canAccess tests for growth-feature-gated resources --

dataset('growthFeatureResources', [
    'growth resources' => [
        CouponResource::class,
        GiftCardResource::class,
        RecipeResource::class,
        ReviewResource::class,
    ],
]);

test('growth-feature resources can be accessed when feature is active', function (string ...$resourceClasses) {
    Feature::define('growth-features', fn () => true);

    foreach ($resourceClasses as $resourceClass) {
        expect($resourceClass::canAccess())->toBeTrue();
    }
})->with('growthFeatureResources');

test('growth-feature resources cannot be accessed when feature is inactive', function (string ...$resourceClasses) {
    Feature::define('growth-features', fn () => false);

    foreach ($resourceClasses as $resourceClass) {
        expect($resourceClass::canAccess())->toBeFalse();
    }
})->with('growthFeatureResources');

test('resources show upgrade badges when tenant has no plan', function () {
    // tenant() returns null → meetsRequirement returns false → shows badge
    expect(EmailCampaignResource::getNavigationBadge())->toBe('PRO');
    expect(CouponResource::getNavigationBadge())->toBe('GROWTH');
    expect(EmailCampaignResource::getNavigationBadgeColor())->toBe('warning');
    expect(CouponResource::getNavigationBadgeColor())->toBe('info');
});
