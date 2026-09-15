<?php

use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Queries\Analytics\CustomerInsightsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('counts customers with active orders and repeat customers in one aggregate', function () {
    $repeatCustomer = Customer::factory()->create();
    $singleOrderCustomer = Customer::factory()->create();
    $cancelledOnlyCustomer = Customer::factory()->create();

    Order::factory()->count(2)->create(['customer_id' => $repeatCustomer->id]);
    Order::factory()->create(['customer_id' => $singleOrderCustomer->id]);
    Order::factory()->cancelled()->create(['customer_id' => $cancelledOnlyCustomer->id]);

    expect(resolve(CustomerInsightsQuery::class)->repeatCustomerCounts())
        ->toBe([
            'total_with_orders' => 2,
            'repeat_customers' => 1,
        ]);
});

test('calculates current and previous month averages while excluding cancelled orders', function () {
    $now = Carbon::create(2026, 9, 15, 12) ?? throw new RuntimeException('Unable to create test date.');
    $customer = Customer::factory()->create();

    Order::factory()->create([
        'customer_id' => $customer->id,
        'total' => 120,
        'created_at' => $now->copy()->startOfMonth()->addDays(2),
    ]);
    Order::factory()->create([
        'customer_id' => $customer->id,
        'total' => 80,
        'created_at' => $now->copy()->subMonth()->startOfMonth()->addDays(2),
    ]);
    Order::factory()->cancelled()->create([
        'customer_id' => $customer->id,
        'total' => 1000,
        'created_at' => $now->copy()->startOfMonth()->addDays(3),
    ]);

    expect(resolve(CustomerInsightsQuery::class)->averageOrderValues($now))
        ->toBe([
            'this_month' => 12000.0,
            'last_month' => 8000.0,
        ]);
});

test('returns zero averages when there are no active orders', function () {
    $now = Carbon::create(2026, 9, 15, 12) ?? throw new RuntimeException('Unable to create test date.');

    expect(resolve(CustomerInsightsQuery::class)->averageOrderValues($now))
        ->toBe([
            'this_month' => 0.0,
            'last_month' => 0.0,
        ]);
});
