<?php

use App\Actions\Customers\RewardReferrer;
use App\Enums\Financial\CouponType;
use App\Models\Customers\CustomerReferral;
use App\Models\Financial\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings(['customer_referral_discount_dollars' => 15]);
});

test('mints a single-use fixed coupon and links it to the referral', function () {
    $referral = CustomerReferral::factory()->completed()->create();

    $coupon = resolve(RewardReferrer::class)($referral);

    expect($coupon->code)->toMatch('/^REF-[A-Z0-9]{6}$/')
        ->and($coupon->type)->toBe(CouponType::Fixed)
        ->and($coupon->fixed_amount->dollars())->toBe(15.0)
        ->and($coupon->max_uses)->toBe(1)
        ->and($referral->fresh()->reward_coupon_id)->toBe($coupon->id);
});

test('does nothing when the referral already has a reward coupon', function () {
    $referral = CustomerReferral::factory()->completed()->create();
    $reward = resolve(RewardReferrer::class);

    $first = $reward($referral);
    $second = $reward($referral->fresh());
    $stale = $reward($referral);

    expect($second->is($first))->toBeTrue()
        ->and($stale->is($first))->toBeTrue()
        ->and(Coupon::query()->count())->toBe(1);
});
