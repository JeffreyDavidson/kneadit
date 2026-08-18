<?php

use App\Models\Financial\Coupon;
use App\Models\Financial\CouponTransaction;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->user = User::factory()->owner()->create();
});

test('coupon has transactions relationship', function () {
    $coupon = Coupon::factory()->create();

    CouponTransaction::factory()->recycle($coupon)->create();

    expect($coupon->transactions)->toHaveCount(1);
});

test('coupon has orders relationship', function () {
    $coupon = Coupon::factory()->create();

    Order::factory()->recycle(test()->user)->create(['coupon_id' => $coupon->id]);

    expect($coupon->orders)->toHaveCount(1);
});

test('active scope returns only active coupons', function () {
    Coupon::factory()->active()->create();
    Coupon::factory()->inactive()->create();

    expect(Coupon::query()->active()->count())->toBe(1);
});

test('valid scope filters by active dates and usage limits', function () {
    // Valid coupon: active, not expired, within limits
    Coupon::factory()->create([
        'is_active' => true,
        'starts_at' => now()->subDay(),
        'expires_at' => now()->addDay(),
        'max_uses' => 10,
        'used_count' => 5,
    ]);

    // Expired coupon
    Coupon::factory()->expired()->create();

    // Inactive coupon
    Coupon::factory()->inactive()->create();

    // Max uses reached
    Coupon::factory()->create([
        'max_uses' => 5,
        'used_count' => 5,
    ]);

    expect(Coupon::query()->valid()->count())->toBe(1);
});

test('valid scope allows null starts_at and expires_at', function () {
    Coupon::factory()->create([
        'is_active' => true,
        'starts_at' => null,
        'expires_at' => null,
        'max_uses' => null,
    ]);

    expect(Coupon::query()->valid()->count())->toBe(1);
});
