<?php

use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Queries\Loyalty\TopLoyaltyCustomersQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('returns customers ranked by loyalty point balance', function () {
    $topCustomer = Customer::factory()->create();
    LoyaltyPoint::factory()->recycle($topCustomer)->earned(300)->create();

    $secondCustomer = Customer::factory()->create();
    LoyaltyPoint::factory()->recycle($secondCustomer)->earned(100)->create();

    $result = TopLoyaltyCustomersQuery::get(limit: 10);

    expect($result)->toHaveCount(2)
        ->and($result->firstOrFail()->id)->toBe($topCustomer->id)
        ->and((int) $result->firstOrFail()->balance)->toBe(300)
        ->and($result->last()->id)->toBe($secondCustomer->id);
});

test('subtracts redeemed points from balance', function () {
    $customer = Customer::factory()->create();
    LoyaltyPoint::factory()->recycle($customer)->earned(200)->create();
    LoyaltyPoint::factory()->recycle($customer)->redeemed(50)->create();

    $result = TopLoyaltyCustomersQuery::get();

    expect((int) $result->firstOrFail()->balance)->toBe(150)
        ->and((int) $result->firstOrFail()->total_earned)->toBe(200);
});

test('respects the limit parameter', function () {
    Customer::factory()->count(3)->create()->each(function (Customer $customer) {
        LoyaltyPoint::factory()->recycle($customer)->earned(100)->create();
    });

    $result = TopLoyaltyCustomersQuery::get(limit: 2);

    expect($result)->toHaveCount(2);
});

test('excludes customers with no loyalty points', function () {
    Customer::factory()->create();

    $withPoints = Customer::factory()->create();
    LoyaltyPoint::factory()->recycle($withPoints)->earned(50)->create();

    $result = TopLoyaltyCustomersQuery::get();

    expect($result)->toHaveCount(1)
        ->and($result->firstOrFail()->id)->toBe($withPoints->id);
});
