<?php

use App\Contracts\Tenants\LegacyCouponImporter;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => setUpTenantTest());

it('imports coupons and returns their current ids', function () {
    $result = resolve(LegacyCouponImporter::class)->import([
        [
            'id' => 25,
            'code' => 'welcome5',
            'type' => 'fixed_amount',
            'value' => '5.00',
            'minimum_order' => '20.00',
            'times_used' => 2,
        ],
    ]);

    $couponId = (int) DB::table('coupons')->where('code', 'WELCOME5')->value('id');

    expect($result)->toBe([25 => $couponId]);
    test()->assertDatabaseHas('coupons', [
        'id' => $couponId,
        'code' => 'WELCOME5',
        'type' => 'fixed',
        'fixed_amount' => 500,
        'min_order_amount' => 2000,
        'used_count' => 2,
    ]);
});

it('updates existing coupons without duplicating them', function () {
    $importer = resolve(LegacyCouponImporter::class);
    $coupon = [['id' => 25, 'code' => 'welcome5', 'type' => 'fixed_amount', 'value' => '5.00']];

    $first = $importer->import($coupon);
    $second = $importer->import($coupon);

    expect($second)->toEqual($first);
    test()->assertDatabaseCount('coupons', 1);
});
