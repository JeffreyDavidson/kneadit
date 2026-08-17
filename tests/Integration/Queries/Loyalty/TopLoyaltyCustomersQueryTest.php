<?php

use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Queries\Loyalty\TopLoyaltyCustomersQuery;
use App\Support\DatabaseValue;
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
        ->and($result->pluck('id')->all())->toBe([$topCustomer->id, $secondCustomer->id])
        ->and(DatabaseValue::int($result->firstOrFail()->getAttribute('balance')))->toBe(300);
});

test('subtracts redeemed points from balance', function () {
    $customer = Customer::factory()->create();
    LoyaltyPoint::factory()->recycle($customer)->earned(200)->create();
    LoyaltyPoint::factory()->recycle($customer)->redeemed(50)->create();

    $result = TopLoyaltyCustomersQuery::get();

    expect(DatabaseValue::int($result->firstOrFail()->getAttribute('balance')))->toBe(150)
        ->and(DatabaseValue::int($result->firstOrFail()->getAttribute('total_earned')))->toBe(200);
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
