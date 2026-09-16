<?php

use App\Enums\Staff\UserRole;
use App\Models\Content\BlogPost;
use App\Models\Content\GalleryPhoto;
use App\Models\Content\SocialPost;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\ContactMessage;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerPhoto;
use App\Models\Customers\WaitlistEntry;
use App\Models\Engagement\CustomerCampaign;
use App\Models\Engagement\EmailCampaign;
use App\Models\Engagement\LoyaltyReward;
use App\Models\Engagement\Review;
use App\Models\Engagement\Survey;
use App\Models\Financial\Coupon;
use App\Models\Financial\Expense;
use App\Models\Financial\GiftCard;
use App\Models\Financial\Income;
use App\Models\Inventory\Category;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Inventory\Supplier;
use App\Models\Operations\BlockedDate;
use App\Models\Operations\CapacityLimit;
use App\Models\Operations\Holiday;
use App\Models\Orders\Order;
use App\Models\Platform\Setting;
use App\Models\Staff\User;
use App\Policies\Content\BlogPostPolicy;
use App\Policies\Content\CustomerPhotoPolicy;
use App\Policies\Content\GalleryPhotoPolicy;
use App\Policies\Customers\CateringInquiryPolicy;
use App\Policies\Customers\ContactMessagePolicy;
use App\Policies\Customers\CustomerPolicy;
use App\Policies\Customers\ReviewPolicy;
use App\Policies\Customers\WaitlistEntryPolicy;
use App\Policies\Engagement\CouponPolicy;
use App\Policies\Engagement\CustomerCampaignPolicy;
use App\Policies\Engagement\LoyaltyRewardPolicy;
use App\Policies\Engagement\SocialPostPolicy;
use App\Policies\Engagement\SurveyPolicy;
use App\Policies\Financial\ExpensePolicy;
use App\Policies\Financial\GiftCardPolicy;
use App\Policies\Financial\IncomePolicy;
use App\Policies\Inventory\CategoryPolicy;
use App\Policies\Inventory\IngredientPolicy;
use App\Policies\Inventory\ProductPolicy;
use App\Policies\Inventory\RecipePolicy;
use App\Policies\Inventory\SupplierPolicy;
use App\Policies\Operations\BlockedDatePolicy;
use App\Policies\Operations\CapacityLimitPolicy;
use App\Policies\Operations\HolidayPolicy;
use App\Policies\Operations\SettingPolicy;
use App\Policies\Orders\OrderPolicy;
use App\Policies\Platform\EmailCampaignPolicy;

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
        [BlockedDatePolicy::class, BlockedDate::class],
        [CapacityLimitPolicy::class, CapacityLimit::class],
        [HolidayPolicy::class, Holiday::class],
        [SettingPolicy::class, Setting::class],
    ]],
    'Customer and content policies' => [[
        [CateringInquiryPolicy::class, CateringInquiry::class],
        [ContactMessagePolicy::class, ContactMessage::class],
        [CustomerPhotoPolicy::class, CustomerPhoto::class],
        [CustomerPolicy::class, Customer::class],
        [GalleryPhotoPolicy::class, GalleryPhoto::class],
        [ReviewPolicy::class, Review::class],
        [WaitlistEntryPolicy::class, WaitlistEntry::class],
    ]],
    'Financial and inventory policies' => [[
        [CategoryPolicy::class, Category::class],
        [ExpensePolicy::class, Expense::class],
        [GiftCardPolicy::class, GiftCard::class],
        [IncomePolicy::class, Income::class],
        [IngredientPolicy::class, Ingredient::class],
        [RecipePolicy::class, Recipe::class],
        [SupplierPolicy::class, Supplier::class],
    ]],
    'Engagement and campaign policies' => [[
        [CouponPolicy::class, Coupon::class],
        [CustomerCampaignPolicy::class, CustomerCampaign::class],
        [EmailCampaignPolicy::class, EmailCampaign::class],
        [LoyaltyRewardPolicy::class, LoyaltyReward::class],
        [SocialPostPolicy::class, SocialPost::class],
        [SurveyPolicy::class, Survey::class],
    ]],
]);

dataset('staffRolePolicyGroups', [
    'Staff policies' => [[
        [BlogPostPolicy::class, BlogPost::class],
        [OrderPolicy::class, Order::class],
        [ProductPolicy::class, Product::class],
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
