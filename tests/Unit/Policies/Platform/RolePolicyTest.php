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

dataset('managerRolePolicyGroups', [
    'Operations policies' => [[
        [App\Policies\Operations\BlockedDatePolicy::class, App\Models\Operations\BlockedDate::class],
        [App\Policies\Operations\CapacityLimitPolicy::class, App\Models\Operations\CapacityLimit::class],
        [App\Policies\Operations\HolidayPolicy::class, App\Models\Operations\Holiday::class],
        [App\Policies\Operations\SettingPolicy::class, App\Models\Platform\Setting::class],
    ]],
    'Customer and content policies' => [[
        [App\Policies\Customers\CateringInquiryPolicy::class, App\Models\Customers\CateringInquiry::class],
        [App\Policies\Customers\ContactMessagePolicy::class, App\Models\Customers\ContactMessage::class],
        [App\Policies\Content\CustomerPhotoPolicy::class, App\Models\Customers\CustomerPhoto::class],
        [App\Policies\Customers\CustomerPolicy::class, App\Models\Customers\Customer::class],
        [App\Policies\Content\GalleryPhotoPolicy::class, App\Models\Content\GalleryPhoto::class],
        [App\Policies\Customers\ReviewPolicy::class, App\Models\Engagement\Review::class],
        [App\Policies\Customers\WaitlistEntryPolicy::class, App\Models\Customers\WaitlistEntry::class],
    ]],
    'Financial and inventory policies' => [[
        [App\Policies\Inventory\CategoryPolicy::class, App\Models\Inventory\Category::class],
        [App\Policies\Financial\ExpensePolicy::class, App\Models\Financial\Expense::class],
        [App\Policies\Financial\GiftCardPolicy::class, App\Models\Financial\GiftCard::class],
        [App\Policies\Financial\IncomePolicy::class, App\Models\Financial\Income::class],
        [App\Policies\Inventory\IngredientPolicy::class, App\Models\Inventory\Ingredient::class],
        [App\Policies\Inventory\RecipePolicy::class, App\Models\Inventory\Recipe::class],
        [App\Policies\Inventory\SupplierPolicy::class, App\Models\Inventory\Supplier::class],
    ]],
    'Engagement and campaign policies' => [[
        [App\Policies\Engagement\CouponPolicy::class, App\Models\Financial\Coupon::class],
        [App\Policies\Engagement\CustomerCampaignPolicy::class, App\Models\Engagement\CustomerCampaign::class],
        [App\Policies\Platform\EmailCampaignPolicy::class, App\Models\Engagement\EmailCampaign::class],
        [App\Policies\Engagement\LoyaltyRewardPolicy::class, App\Models\Engagement\LoyaltyReward::class],
        [App\Policies\Engagement\SocialPostPolicy::class, App\Models\Content\SocialPost::class],
        [App\Policies\Engagement\SurveyPolicy::class, App\Models\Engagement\Survey::class],
    ]],
]);

dataset('staffRolePolicyGroups', [
    'Staff policies' => [[
        [App\Policies\Content\BlogPostPolicy::class, App\Models\Content\BlogPost::class],
        [App\Policies\Orders\OrderPolicy::class, App\Models\Orders\Order::class],
        [App\Policies\Inventory\ProductPolicy::class, App\Models\Inventory\Product::class],
    ]],
]);

test('manager policies deny staff users', function (array $policies) {
    $user = new User(['role' => UserRole::Staff]);

    foreach ($policies as [$policyClass, $modelClass]) {
        $model = new $modelClass;

        expect(rolePolicyResult($policyClass, 'viewAny', $user))->toBeFalse()
            ->and(rolePolicyResult($policyClass, 'view', $user, $model))->toBeFalse()
            ->and(rolePolicyResult($policyClass, 'create', $user))->toBeFalse()
            ->and(rolePolicyResult($policyClass, 'update', $user, $model))->toBeFalse()
            ->and(rolePolicyResult($policyClass, 'delete', $user, $model))->toBeFalse();
    }
})->with('managerRolePolicyGroups');

test('manager policies allow owner users', function (array $policies) {
    $user = new User(['role' => UserRole::Owner]);

    foreach ($policies as [$policyClass, $modelClass]) {
        $model = new $modelClass;

        expect(rolePolicyResult($policyClass, 'viewAny', $user))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'view', $user, $model))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'create', $user))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'update', $user, $model))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'delete', $user, $model))->toBeTrue();
    }
})->with('managerRolePolicyGroups');

test('manager policies allow manager users', function (array $policies) {
    $user = new User(['role' => UserRole::Manager]);

    foreach ($policies as [$policyClass, $modelClass]) {
        $model = new $modelClass;

        expect(rolePolicyResult($policyClass, 'viewAny', $user))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'view', $user, $model))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'create', $user))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'update', $user, $model))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'delete', $user, $model))->toBeTrue();
    }
})->with('managerRolePolicyGroups');

test('staff policies allow staff users', function (array $policies) {
    $user = new User(['role' => UserRole::Staff]);

    foreach ($policies as [$policyClass, $modelClass]) {
        $model = new $modelClass;

        expect(rolePolicyResult($policyClass, 'viewAny', $user))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'view', $user, $model))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'create', $user))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'update', $user, $model))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'delete', $user, $model))->toBeTrue();
    }
})->with('staffRolePolicyGroups');

test('staff policies allow owner users', function (array $policies) {
    $user = new User(['role' => UserRole::Owner]);

    foreach ($policies as [$policyClass, $modelClass]) {
        $model = new $modelClass;

        expect(rolePolicyResult($policyClass, 'viewAny', $user))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'view', $user, $model))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'create', $user))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'update', $user, $model))->toBeTrue()
            ->and(rolePolicyResult($policyClass, 'delete', $user, $model))->toBeTrue();
    }
})->with('staffRolePolicyGroups');
