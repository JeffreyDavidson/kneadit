<?php

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('active scope returns only active coupons', function () {
    $active = Coupon::factory()->create(['is_active' => true]);
    Coupon::factory()->create(['is_active' => false]);

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
