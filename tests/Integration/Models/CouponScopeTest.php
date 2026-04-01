<?php

use App\Models\Coupon;
use App\Services\Coupon\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('active scope returns only active coupons', function () {
    $active = Coupon::factory()->create(['is_active' => true]);
    Coupon::factory()->inactive()->create();

    $results = Coupon::query()->active()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($active->id);
});

test('valid scope excludes expired coupons', function () {
    $valid = Coupon::factory()->create();
    Coupon::factory()->expired()->create();

    $results = Coupon::query()->valid()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($valid->id);
});

test('isValid method agrees with valid scope', function () {
    $valid = Coupon::factory()->create();
    $expired = Coupon::factory()->expired()->create();
    $inactive = Coupon::factory()->inactive()->create();
    $maxedOut = Coupon::factory()->create(['max_uses' => 1, 'used_count' => 1]);

    $scopeIds = Coupon::query()->valid()->pluck('id')->sort()->values();
    $methodIds = Coupon::all()
        ->filter(fn (Coupon $coupon) => resolve(CouponService::class)->isValid($coupon))
        ->pluck('id')->sort()->values();

    expect($methodIds->all())->toBe($scopeIds->all());
});
