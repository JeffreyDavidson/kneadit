<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

dataset('managerPolicies', [
    'BlockedDate' => ['App\Policies\BlockedDatePolicy', 'App\Models\BlockedDate'],
    'CapacityLimit' => ['App\Policies\CapacityLimitPolicy', 'App\Models\CapacityLimit'],
    'Category' => ['App\Policies\CategoryPolicy', 'App\Models\Category'],
    'ContactMessage' => ['App\Policies\ContactMessagePolicy', 'App\Models\ContactMessage'],
    'Coupon' => ['App\Policies\CouponPolicy', 'App\Models\Coupon'],
    'CustomerPhoto' => ['App\Policies\CustomerPhotoPolicy', 'App\Models\CustomerPhoto'],
    'Customer' => ['App\Policies\CustomerPolicy', 'App\Models\Customer'],
    'EmailCampaign' => ['App\Policies\EmailCampaignPolicy', 'App\Models\EmailCampaign'],
    'Expense' => ['App\Policies\ExpensePolicy', 'App\Models\Expense'],
    'GalleryPhoto' => ['App\Policies\GalleryPhotoPolicy', 'App\Models\GalleryPhoto'],
    'GiftCard' => ['App\Policies\GiftCardPolicy', 'App\Models\GiftCard'],
    'Holiday' => ['App\Policies\HolidayPolicy', 'App\Models\Holiday'],
    'Income' => ['App\Policies\IncomePolicy', 'App\Models\Income'],
    'Ingredient' => ['App\Policies\IngredientPolicy', 'App\Models\Ingredient'],
    'LoyaltyReward' => ['App\Policies\LoyaltyRewardPolicy', 'App\Models\LoyaltyReward'],
    'Recipe' => ['App\Policies\RecipePolicy', 'App\Models\Recipe'],
    'Review' => ['App\Policies\ReviewPolicy', 'App\Models\Review'],
    'Setting' => ['App\Policies\SettingPolicy', 'App\Models\Setting'],
    'SocialPost' => ['App\Policies\SocialPostPolicy', 'App\Models\SocialPost'],
    'Supplier' => ['App\Policies\SupplierPolicy', 'App\Models\Supplier'],
    'Survey' => ['App\Policies\SurveyPolicy', 'App\Models\Survey'],
    'WaitlistEntry' => ['App\Policies\WaitlistEntryPolicy', 'App\Models\WaitlistEntry'],
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
