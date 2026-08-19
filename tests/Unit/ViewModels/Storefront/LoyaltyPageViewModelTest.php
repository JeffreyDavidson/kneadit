<?php

use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyReward;
use App\ValueObjects\LoyaltyBalance;
use App\ViewModels\Storefront\LoyaltyPageViewModel;

function makeLoyaltyTenantSettings(): App\Services\Settings\TenantSettings
{
    return makeTenantSettings(
        engagement: makeEngagementSettings(['birthdayCouponEnabled' => true]),
    );
}

/**
 * @param array{settings?: App\Services\Settings\TenantSettings, customer?: ?Customer, balance?: LoyaltyBalance, history?: Illuminate\Support\Collection<int, App\Models\Engagement\LoyaltyPoint>, rewards?: Illuminate\Support\Collection<int, LoyaltyReward>, content?: array<string, string>, howSteps?: array<int, array<string, string>>, customerNotFound?: bool} $overrides
 */
function makeLoyaltyVm(array $overrides = []): LoyaltyPageViewModel
{
    $defaults = [
        'settings' => makeLoyaltyTenantSettings(),
        'customer' => null,
        'balance' => new LoyaltyBalance(earned: 0, redeemed: 0, adjusted: 0),
        'history' => collect(),
        'rewards' => collect(),
        'customerNotFound' => false,
    ];

    $args = array_merge($defaults, $overrides);

    return new LoyaltyPageViewModel(...$args);
}

function makeFakeReward(int $pointsRequired, string $name = 'Reward'): LoyaltyReward
{
    $reward = new LoyaltyReward;
    $reward->forceFill([
        'name' => $name,
        'points_required' => $pointsRequired,
        'is_active' => true,
    ]);

    return $reward;
}

test('totalPoints and lifetimeEarned come from balance', function () {
    $vm = makeLoyaltyVm([
        'balance' => new LoyaltyBalance(earned: 2000, redeemed: 300, adjusted: 100),
    ]);

    expect($vm->totalPoints)->toBe(1800)
        ->and($vm->lifetimeEarned)->toBe(2000);
});

test('nextReward is first reward above current points', function () {
    $rewards = collect([
        makeFakeReward(100, 'Small'),
        makeFakeReward(500, 'Medium'),
        makeFakeReward(1000, 'Large'),
    ]);

    $vm = makeLoyaltyVm([
        'balance' => new LoyaltyBalance(earned: 200, redeemed: 0, adjusted: 0),
        'rewards' => $rewards,
    ]);

    if ($vm->nextReward === null) {
        throw new RuntimeException('Expected a next reward.');
    }

    expect($vm->nextReward->name)->toBe('Medium');
});

test('nextReward is null when all rewards are reachable', function () {
    $rewards = collect([
        makeFakeReward(100, 'Small'),
        makeFakeReward(500, 'Medium'),
    ]);

    $vm = makeLoyaltyVm([
        'balance' => new LoyaltyBalance(earned: 1000, redeemed: 0, adjusted: 0),
        'rewards' => $rewards,
    ]);

    expect($vm->nextReward)->toBeNull();
});

test('nextReward is null when no rewards exist', function () {
    $vm = makeLoyaltyVm(['rewards' => (new LoyaltyReward)->newCollection()]);

    expect($vm->nextReward)->toBeNull();
});

test('nextRewardProgressPercent computes correctly', function () {
    $vm = makeLoyaltyVm([
        'balance' => new LoyaltyBalance(earned: 250, redeemed: 0, adjusted: 0),
        'rewards' => collect([makeFakeReward(500)]),
    ]);

    expect($vm->nextRewardProgressPercent())->toBe(50.0);
});

test('nextRewardProgressPercent caps at 100', function () {
    $vm = makeLoyaltyVm([
        'balance' => new LoyaltyBalance(earned: 600, redeemed: 0, adjusted: 0),
        'rewards' => collect([makeFakeReward(500), makeFakeReward(1000)]),
    ]);

    expect($vm->nextRewardProgressPercent())->toBe(60.0);
});

test('nextRewardProgressPercent returns 0 when no next reward', function () {
    $vm = makeLoyaltyVm([
        'balance' => new LoyaltyBalance(earned: 1000, redeemed: 0, adjusted: 0),
        'rewards' => collect([makeFakeReward(500)]),
    ]);

    expect($vm->nextRewardProgressPercent())->toBe(0.0);
});

test('pointsToNextReward computes the integer difference', function () {
    $vm = makeLoyaltyVm([
        'balance' => new LoyaltyBalance(earned: 750, redeemed: 0, adjusted: 0),
        'rewards' => collect([makeFakeReward(1000)]),
    ]);

    expect($vm->pointsToNextReward())->toBe(250);
});

test('pointsToNextReward returns 0 when there is no next reward', function () {
    $vm = makeLoyaltyVm([
        'balance' => new LoyaltyBalance(earned: 1000, redeemed: 0, adjusted: 0),
        'rewards' => collect([makeFakeReward(500)]),
    ]);

    expect($vm->pointsToNextReward())->toBe(0);
});

test('canRedeem returns true when customer has enough points', function () {
    $customer = new Customer;

    $vm = makeLoyaltyVm([
        'customer' => $customer,
        'balance' => new LoyaltyBalance(earned: 500, redeemed: 0, adjusted: 0),
    ]);

    expect($vm->canRedeem(makeFakeReward(500)))->toBeTrue()
        ->and($vm->canRedeem(makeFakeReward(300)))->toBeTrue();
});

test('canRedeem returns false when customer has insufficient points', function () {
    $customer = new Customer;

    $vm = makeLoyaltyVm([
        'customer' => $customer,
        'balance' => new LoyaltyBalance(earned: 200, redeemed: 0, adjusted: 0),
    ]);

    expect($vm->canRedeem(makeFakeReward(500)))->toBeFalse();
});

test('canRedeem returns false when no customer', function () {
    $vm = makeLoyaltyVm([
        'balance' => new LoyaltyBalance(earned: 1000, redeemed: 0, adjusted: 0),
    ]);

    expect($vm->canRedeem(makeFakeReward(100)))->toBeFalse();
});

test('historyEntrySign returns minus for Redeemed and plus for others', function () {
    $vm = makeLoyaltyVm();

    expect($vm->historyEntrySign(LoyaltyPointType::Redeemed))->toBe('-')
        ->and($vm->historyEntrySign(LoyaltyPointType::Earned))->toBe('+')
        ->and($vm->historyEntrySign(LoyaltyPointType::Adjusted))->toBe('+');
});

test('historyEntryColorClass returns correct Tailwind classes per enum case', function () {
    $vm = makeLoyaltyVm();

    expect($vm->historyEntryColorClass(LoyaltyPointType::Earned))->toBe('text-green-600')
        ->and($vm->historyEntryColorClass(LoyaltyPointType::Redeemed))->toBe('text-red-600')
        ->and($vm->historyEntryColorClass(LoyaltyPointType::Adjusted))->toBe('text-yellow-600');
});

test('hasCustomer is true when customer is present', function () {
    $vm = makeLoyaltyVm([
        'customer' => new Customer,
    ]);

    expect($vm->hasCustomer)->toBeTrue();
});

test('hasCustomer is false when customer is null', function () {
    $vm = makeLoyaltyVm();

    expect($vm->hasCustomer)->toBeFalse();
});

test('customerNotFound reflects constructor value', function () {
    $vmFound = makeLoyaltyVm(['customerNotFound' => false]);
    $vmNotFound = makeLoyaltyVm(['customerNotFound' => true]);

    expect($vmFound->customerNotFound)->toBeFalse()
        ->and($vmNotFound->customerNotFound)->toBeTrue();
});
