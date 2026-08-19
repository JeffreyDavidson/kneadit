<?php

use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Queries\Customers\CustomerInsightsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Date::setTestNow('2026-08-18 12:00:00');
});

afterEach(fn () => Date::setTestNow());

test('calculates customer and order insights', function () {
    $repeat = Customer::factory()->create(['created_at' => Date::now()]);
    $single = Customer::factory()->create(['created_at' => Date::now()->subWeek()]);
    Order::factory()->recycle($repeat)->delivered()->count(2)->create(['total' => 2000, 'created_at' => Date::now()]);
    Order::factory()->recycle($single)->delivered()->create(['total' => 1000, 'created_at' => Date::now()->subMonth()]);

    $query = resolve(CustomerInsightsQuery::class);

    expect($query->newCustomersThisWeek())->toBe(1)
        ->and($query->repeatCustomerRate())->toBe(50.0)
        ->and($query->averageOrderValue())->toBe(['value' => 2000.0, 'trend' => 'up']);
});
