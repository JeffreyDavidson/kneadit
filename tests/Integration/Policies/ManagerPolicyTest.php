<?php

use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

dataset('managerPolicies', [
    'BlockedDate' => [\App\Policies\Operations\BlockedDatePolicy::class, \App\Models\Operations\BlockedDate::class],
    'CapacityLimit' => [\App\Policies\Operations\CapacityLimitPolicy::class, \App\Models\Operations\CapacityLimit::class],
    'Category' => [\App\Policies\Inventory\CategoryPolicy::class, \App\Models\Inventory\Category::class],
    'ContactMessage' => [\App\Policies\Customers\ContactMessagePolicy::class, \App\Models\Customers\ContactMessage::class],
    'Coupon' => [\App\Policies\Engagement\CouponPolicy::class, \App\Models\Financial\Coupon::class],
    'CustomerPhoto' => [\App\Policies\Content\CustomerPhotoPolicy::class, \App\Models\Customers\CustomerPhoto::class],
    'Customer' => [\App\Policies\Customers\CustomerPolicy::class, \App\Models\Customers\Customer::class],
    'EmailCampaign' => [\App\Policies\Platform\EmailCampaignPolicy::class, \App\Models\Engagement\EmailCampaign::class],
    'Expense' => [\App\Policies\Financial\ExpensePolicy::class, \App\Models\Financial\Expense::class],
    'GalleryPhoto' => [\App\Policies\Content\GalleryPhotoPolicy::class, \App\Models\Content\GalleryPhoto::class],
    'GiftCard' => [\App\Policies\Financial\GiftCardPolicy::class, \App\Models\Financial\GiftCard::class],
    'Holiday' => [\App\Policies\Operations\HolidayPolicy::class, \App\Models\Operations\Holiday::class],
    'Income' => [\App\Policies\Financial\IncomePolicy::class, \App\Models\Financial\Income::class],
    'Ingredient' => [\App\Policies\Inventory\IngredientPolicy::class, \App\Models\Inventory\Ingredient::class],
    'LoyaltyReward' => [\App\Policies\Engagement\LoyaltyRewardPolicy::class, \App\Models\Engagement\LoyaltyReward::class],
    'Recipe' => [\App\Policies\Inventory\RecipePolicy::class, \App\Models\Inventory\Recipe::class],
    'Review' => [\App\Policies\Customers\ReviewPolicy::class, \App\Models\Engagement\Review::class],
    'Setting' => [\App\Policies\Operations\SettingPolicy::class, \App\Models\Platform\Setting::class],
    'SocialPost' => [\App\Policies\Engagement\SocialPostPolicy::class, \App\Models\Content\SocialPost::class],
    'Supplier' => [\App\Policies\Inventory\SupplierPolicy::class, \App\Models\Inventory\Supplier::class],
    'Survey' => [\App\Policies\Engagement\SurveyPolicy::class, \App\Models\Engagement\Survey::class],
    'WaitlistEntry' => [\App\Policies\Customers\WaitlistEntryPolicy::class, \App\Models\Customers\WaitlistEntry::class],
]);

test('manager policies deny staff users', function (string $policyClass, string $modelClass) {
    $policy = new $policyClass;
    $user = User::factory()->staff()->create();
    $model = new $modelClass;

    expect($policy->viewAny($user))->toBeFalse()
        ->and($policy->view($user, $model))->toBeFalse()
        ->and($policy->create($user))->toBeFalse()
        ->and($policy->update($user, $model))->toBeFalse()
        ->and($policy->delete($user, $model))->toBeFalse();
})->with('managerPolicies');

test('manager policies allow owner users', function (string $policyClass, string $modelClass) {
    $policy = new $policyClass;
    $user = User::factory()->owner()->create();
    $model = new $modelClass;

    expect($policy->viewAny($user))->toBeTrue()
        ->and($policy->view($user, $model))->toBeTrue()
        ->and($policy->create($user))->toBeTrue()
        ->and($policy->update($user, $model))->toBeTrue()
        ->and($policy->delete($user, $model))->toBeTrue();
})->with('managerPolicies');
test('manager policies allow manager users', function (string $policyClass, string $modelClass) {
    $policy = new $policyClass;
    $user = User::factory()->manager()->create();
    $model = new $modelClass;

    expect($policy->viewAny($user))->toBeTrue()
        ->and($policy->view($user, $model))->toBeTrue()
        ->and($policy->create($user))->toBeTrue()
        ->and($policy->update($user, $model))->toBeTrue()
        ->and($policy->delete($user, $model))->toBeTrue();
})->with('managerPolicies');
