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
use Filament\Resources\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

function resourceCanAccess(string $resourceClass): bool
{
    if (! is_subclass_of($resourceClass, Resource::class)) {
        throw new InvalidArgumentException("{$resourceClass} is not a Filament resource.");
    }

    return $resourceClass::canAccess();
}

// -- canAccess tests for pro-feature-gated resources --

dataset('proFeatureResources', [
    'EmailCampaignResource' => [EmailCampaignResource::class],
    'LoyaltyRewardResource' => [LoyaltyRewardResource::class],
    'IngredientResource' => [IngredientResource::class],
    'SocialPostResource' => [SocialPostResource::class],
]);

test('pro-feature resource can be accessed when feature is active', function (string $resourceClass) {
    Feature::define('pro-features', fn () => true);

    expect(resourceCanAccess($resourceClass))->toBeTrue();
})->with('proFeatureResources');

test('pro-feature resource cannot be accessed when feature is inactive', function (string $resourceClass) {
    Feature::define('pro-features', fn () => false);

    expect(resourceCanAccess($resourceClass))->toBeFalse();
})->with('proFeatureResources');

// -- canAccess tests for growth-feature-gated resources --

dataset('growthFeatureResources', [
    'CouponResource' => [CouponResource::class],
    'GiftCardResource' => [GiftCardResource::class],
    'RecipeResource' => [RecipeResource::class],
    'ReviewResource' => [ReviewResource::class],
]);

test('growth-feature resource can be accessed when feature is active', function (string $resourceClass) {
    Feature::define('growth-features', fn () => true);

    expect(resourceCanAccess($resourceClass))->toBeTrue();
})->with('growthFeatureResources');

test('growth-feature resource cannot be accessed when feature is inactive', function (string $resourceClass) {
    Feature::define('growth-features', fn () => false);

    expect(resourceCanAccess($resourceClass))->toBeFalse();
})->with('growthFeatureResources');

// -- ShowsUpgradeBadge: getNavigationBadge tests --

test('pro-tier resource shows PRO badge when tenant has no plan', function () {
    // tenant() returns null → meetsRequirement returns false → shows badge
    expect(EmailCampaignResource::getNavigationBadge())->toBe('PRO');
});

test('growth-tier resource shows GROWTH badge when tenant has no plan', function () {
    expect(CouponResource::getNavigationBadge())->toBe('GROWTH');
});

// -- ShowsUpgradeBadge: getNavigationBadgeColor tests --

test('pro-tier resource badge color is warning when not met', function () {
    expect(EmailCampaignResource::getNavigationBadgeColor())->toBe('warning');
});

test('growth-tier resource badge color is info when not met', function () {
    expect(CouponResource::getNavigationBadgeColor())->toBe('info');
});
