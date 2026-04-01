<?php

use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

dataset('managerPolicies', [
    'BlockedDate' => ['App\Policies\Operations\BlockedDatePolicy', 'App\Models\Operations\BlockedDate'],
    'CapacityLimit' => ['App\Policies\Operations\CapacityLimitPolicy', 'App\Models\Operations\CapacityLimit'],
    'Category' => ['App\Policies\Inventory\CategoryPolicy', 'App\Models\Inventory\Category'],
    'ContactMessage' => ['App\Policies\Customers\ContactMessagePolicy', 'App\Models\Customers\ContactMessage'],
    'Coupon' => ['App\Policies\Engagement\CouponPolicy', 'App\Models\Financial\Coupon'],
    'CustomerPhoto' => ['App\Policies\Content\CustomerPhotoPolicy', 'App\Models\Customers\CustomerPhoto'],
    'Customer' => ['App\Policies\Customers\CustomerPolicy', 'App\Models\Customers\Customer'],
    'EmailCampaign' => ['App\Policies\Platform\EmailCampaignPolicy', 'App\Models\Engagement\EmailCampaign'],
    'Expense' => ['App\Policies\Financial\ExpensePolicy', 'App\Models\Financial\Expense'],
    'GalleryPhoto' => ['App\Policies\Content\GalleryPhotoPolicy', 'App\Models\Content\GalleryPhoto'],
    'GiftCard' => ['App\Policies\Financial\GiftCardPolicy', 'App\Models\Financial\GiftCard'],
    'Holiday' => ['App\Policies\Operations\HolidayPolicy', 'App\Models\Operations\Holiday'],
    'Income' => ['App\Policies\Financial\IncomePolicy', 'App\Models\Financial\Income'],
    'Ingredient' => ['App\Policies\Inventory\IngredientPolicy', 'App\Models\Inventory\Ingredient'],
    'LoyaltyReward' => ['App\Policies\Engagement\LoyaltyRewardPolicy', 'App\Models\Engagement\LoyaltyReward'],
    'Recipe' => ['App\Policies\Inventory\RecipePolicy', 'App\Models\Inventory\Recipe'],
    'Review' => ['App\Policies\Customers\ReviewPolicy', 'App\Models\Engagement\Review'],
    'Setting' => ['App\Policies\Operations\SettingPolicy', 'App\Models\Platform\Setting'],
    'SocialPost' => ['App\Policies\Engagement\SocialPostPolicy', 'App\Models\Content\SocialPost'],
    'Supplier' => ['App\Policies\Inventory\SupplierPolicy', 'App\Models\Inventory\Supplier'],
    'Survey' => ['App\Policies\Engagement\SurveyPolicy', 'App\Models\Engagement\Survey'],
    'WaitlistEntry' => ['App\Policies\Customers\WaitlistEntryPolicy', 'App\Models\Customers\WaitlistEntry'],
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
