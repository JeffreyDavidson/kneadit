<?php

use App\Enums\Staff\UserRole;
use App\Models\Staff\User;

function rolePolicyResult(string $policyClass, string $ability, User $user, ?object $model = null): bool
{
    $policy = new $policyClass;

    if (! method_exists($policy, $ability)) {
        throw new LogicException("Policy {$policyClass} does not define {$ability}.");
    }

    $result = $model === null
        ? $policy->{$ability}($user)
        : $policy->{$ability}($user, $model);

    if (! is_bool($result)) {
        throw new LogicException("Policy {$policyClass}::{$ability} did not return a boolean.");
    }

    return $result;
}

dataset('managerRolePolicies', [
    'BlockedDate' => [App\Policies\Operations\BlockedDatePolicy::class, App\Models\Operations\BlockedDate::class],
    'CapacityLimit' => [App\Policies\Operations\CapacityLimitPolicy::class, App\Models\Operations\CapacityLimit::class],
    'CateringInquiry' => [App\Policies\Customers\CateringInquiryPolicy::class, App\Models\Customers\CateringInquiry::class],
    'Category' => [App\Policies\Inventory\CategoryPolicy::class, App\Models\Inventory\Category::class],
    'ContactMessage' => [App\Policies\Customers\ContactMessagePolicy::class, App\Models\Customers\ContactMessage::class],
    'Coupon' => [App\Policies\Engagement\CouponPolicy::class, App\Models\Financial\Coupon::class],
    'CustomerPhoto' => [App\Policies\Content\CustomerPhotoPolicy::class, App\Models\Customers\CustomerPhoto::class],
    'Customer' => [App\Policies\Customers\CustomerPolicy::class, App\Models\Customers\Customer::class],
    'CustomerCampaign' => [App\Policies\Engagement\CustomerCampaignPolicy::class, App\Models\Engagement\CustomerCampaign::class],
    'EmailCampaign' => [App\Policies\Platform\EmailCampaignPolicy::class, App\Models\Engagement\EmailCampaign::class],
    'Expense' => [App\Policies\Financial\ExpensePolicy::class, App\Models\Financial\Expense::class],
    'GalleryPhoto' => [App\Policies\Content\GalleryPhotoPolicy::class, App\Models\Content\GalleryPhoto::class],
    'GiftCard' => [App\Policies\Financial\GiftCardPolicy::class, App\Models\Financial\GiftCard::class],
    'Holiday' => [App\Policies\Operations\HolidayPolicy::class, App\Models\Operations\Holiday::class],
    'Income' => [App\Policies\Financial\IncomePolicy::class, App\Models\Financial\Income::class],
    'Ingredient' => [App\Policies\Inventory\IngredientPolicy::class, App\Models\Inventory\Ingredient::class],
    'LoyaltyReward' => [App\Policies\Engagement\LoyaltyRewardPolicy::class, App\Models\Engagement\LoyaltyReward::class],
    'Recipe' => [App\Policies\Inventory\RecipePolicy::class, App\Models\Inventory\Recipe::class],
    'Review' => [App\Policies\Customers\ReviewPolicy::class, App\Models\Engagement\Review::class],
    'Setting' => [App\Policies\Operations\SettingPolicy::class, App\Models\Platform\Setting::class],
    'SocialPost' => [App\Policies\Engagement\SocialPostPolicy::class, App\Models\Content\SocialPost::class],
    'Supplier' => [App\Policies\Inventory\SupplierPolicy::class, App\Models\Inventory\Supplier::class],
    'Survey' => [App\Policies\Engagement\SurveyPolicy::class, App\Models\Engagement\Survey::class],
    'WaitlistEntry' => [App\Policies\Customers\WaitlistEntryPolicy::class, App\Models\Customers\WaitlistEntry::class],
]);

dataset('staffRolePolicies', [
    'BlogPost' => [App\Policies\Content\BlogPostPolicy::class, App\Models\Content\BlogPost::class],
    'Order' => [App\Policies\Orders\OrderPolicy::class, App\Models\Orders\Order::class],
    'Product' => [App\Policies\Inventory\ProductPolicy::class, App\Models\Inventory\Product::class],
]);

test('manager policies deny staff users', function (string $policyClass, string $modelClass) {
    $user = new User(['role' => UserRole::Staff]);
    $model = new $modelClass;

    expect(rolePolicyResult($policyClass, 'viewAny', $user))->toBeFalse()
        ->and(rolePolicyResult($policyClass, 'view', $user, $model))->toBeFalse()
        ->and(rolePolicyResult($policyClass, 'create', $user))->toBeFalse()
        ->and(rolePolicyResult($policyClass, 'update', $user, $model))->toBeFalse()
        ->and(rolePolicyResult($policyClass, 'delete', $user, $model))->toBeFalse();
})->with('managerRolePolicies');

test('manager policies allow owner users', function (string $policyClass, string $modelClass) {
    $user = new User(['role' => UserRole::Owner]);
    $model = new $modelClass;

    expect(rolePolicyResult($policyClass, 'viewAny', $user))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'view', $user, $model))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'create', $user))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'update', $user, $model))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'delete', $user, $model))->toBeTrue();
})->with('managerRolePolicies');

test('manager policies allow manager users', function (string $policyClass, string $modelClass) {
    $user = new User(['role' => UserRole::Manager]);
    $model = new $modelClass;

    expect(rolePolicyResult($policyClass, 'viewAny', $user))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'view', $user, $model))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'create', $user))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'update', $user, $model))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'delete', $user, $model))->toBeTrue();
})->with('managerRolePolicies');

test('staff policies allow staff users', function (string $policyClass, string $modelClass) {
    $user = new User(['role' => UserRole::Staff]);
    $model = new $modelClass;

    expect(rolePolicyResult($policyClass, 'viewAny', $user))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'view', $user, $model))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'create', $user))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'update', $user, $model))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'delete', $user, $model))->toBeTrue();
})->with('staffRolePolicies');

test('staff policies allow owner users', function (string $policyClass, string $modelClass) {
    $user = new User(['role' => UserRole::Owner]);
    $model = new $modelClass;

    expect(rolePolicyResult($policyClass, 'viewAny', $user))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'view', $user, $model))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'create', $user))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'update', $user, $model))->toBeTrue()
        ->and(rolePolicyResult($policyClass, 'delete', $user, $model))->toBeTrue();
})->with('staffRolePolicies');
