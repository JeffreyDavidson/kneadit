<?php

use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyReward;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\LoyaltyBalance;
use App\ViewModels\Storefront\LoyaltyPageViewModel;

function makeLoyaltyTenantSettings(): TenantSettings
{
    return new TenantSettings(
        storeName: 'Test Bakery',
        storeEmail: null,
        storePhone: null,
        storeAddress: null,
        storeWebsite: null,
        storeLogo: null,
        storeTagline: null,
        brandColorPrimary: '#d4920c',
        onboardingCompletedAt: null,
        storefrontTheme: 'classic',
        businessTagline: null,
        aboutUsText: null,
        heroImage: null,
        heroStyle: 'split',
        allergyDisclaimer: null,
        cateringHeroImage: null,
        loyaltyHeroImage: null,
        giftCardsHeroImage: null,
        leadTimeHours: 24,
        deliveryEnabled: true,
        freeDeliveryMinimum: '50',
        deliveryFeeTiers: [],
        paymentMethodsAccepted: [],
        operatingHours: [],
        faqItems: [],
        loyaltyProgramName: 'Rewards',
        loyaltyPointsPerDollar: '10',
        loyaltyEnabled: true,
        cateringMinimumGuests: '10',
        cateringLeadTimeDays: '14',
        cateringEventTypes: ['Wedding', 'Corporate Event'],
        socialMediaLinks: [],
        homepageSections: [],
        cateringEnabled: false,
        storePhoto: null,
        announcementEnabled: false,
        announcementText: '',
        announcementType: 'info',
        showPolicies: false,
        cancellationPolicy: '',
        depositPolicy: '',
        refundPolicy: '',
        pickupPolicy: '',
        additionalTerms: '',
        birthdayProgramEnabled: false,
        birthdayCouponEnabled: true,
        birthdayDiscountPercentage: 15,
        birthdayCouponValidDays: 7,
        reviewRequestsEnabled: false,
        reviewRequestDelayHours: 24,
        repeatRemindersEnabled: false,
        repeatReminderDays: 30,
    );
}

function makeLoyaltyVm(array $overrides = []): LoyaltyPageViewModel
{
    $defaults = [
        'settings' => makeLoyaltyTenantSettings(),
        'customer' => null,
        'balance' => new LoyaltyBalance(earned: 0, redeemed: 0, adjusted: 0),
        'history' => collect(),
        'rewards' => collect(),
        'content' => [],
        'howSteps' => [],
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

test('formattedTotalPoints uses number_format', function () {
    $vm = makeLoyaltyVm([
        'balance' => new LoyaltyBalance(earned: 1500, redeemed: 0, adjusted: 0),
    ]);

    expect($vm->formattedTotalPoints)->toBe('1,500');
});

test('formattedLifetimeEarned uses number_format', function () {
    $vm = makeLoyaltyVm([
        'balance' => new LoyaltyBalance(earned: 12345, redeemed: 0, adjusted: 0),
    ]);

    expect($vm->formattedLifetimeEarned)->toBe('12,345');
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
    $vm = makeLoyaltyVm(['rewards' => collect()]);

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

test('formattedPointsToNextReward computes difference', function () {
    $vm = makeLoyaltyVm([
        'balance' => new LoyaltyBalance(earned: 750, redeemed: 0, adjusted: 0),
        'rewards' => collect([makeFakeReward(1000)]),
    ]);

    expect($vm->formattedPointsToNextReward())->toBe('250');
});

test('formattedNextRewardRequired formats points', function () {
    $vm = makeLoyaltyVm([
        'balance' => new LoyaltyBalance(earned: 500, redeemed: 0, adjusted: 0),
        'rewards' => collect([makeFakeReward(2500)]),
    ]);

    expect($vm->formattedNextRewardRequired())->toBe('2,500');
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

test('formattedEntryPoints formats with number_format', function () {
    $vm = makeLoyaltyVm();

    expect($vm->formattedEntryPoints(1500))->toBe('1,500')
        ->and($vm->formattedEntryPoints(50))->toBe('50');
});

test('formattedRewardPoints formats with number_format', function () {
    $vm = makeLoyaltyVm();

    expect($vm->formattedRewardPoints(makeFakeReward(2500)))->toBe('2,500');
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
