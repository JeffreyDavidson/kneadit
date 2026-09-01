<?php

use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Reports\Customers\CustomerReport;
use App\ValueObjects\DateRange;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Spatie\Snapshots\assertMatchesSnapshot;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('generates customer report for a date range', function () {
    $range = DateRange::forMonth(2026, 3);
    $report = new CustomerReport;
    $result = $report->generate($range);

    expect($result)->toBeArray()
        ->toHaveKeys(['newCustomers', 'repeatRate', 'topCustomers', 'acquisitionByMonth']);
});

test('report shape matches the recorded snapshot for known inputs', function () {
    // Fixed inputs: 3 customers, 2 with paid orders, 1 with two orders (repeat)
    $alice = Customer::factory()->create([
        'name' => 'Alice Adams',
        'email' => 'alice@example.com',
        'created_at' => '2026-03-05 10:00:00',
    ]);
    $bob = Customer::factory()->create([
        'name' => 'Bob Baker',
        'email' => 'bob@example.com',
        'created_at' => '2026-03-12 10:00:00',
    ]);
    Customer::factory()->create([
        'name' => 'Charlie Cookie',
        'email' => 'charlie@example.com',
        'created_at' => '2026-03-20 10:00:00',
    ]);

    Order::factory()->for($alice)->create([
        'delivery_date' => '2026-03-15',
        'payment_status' => PaymentStatus::Paid,
        'total' => 50.00,
    ]);
    Order::factory()->for($alice)->create([
        'delivery_date' => '2026-03-22',
        'payment_status' => PaymentStatus::Paid,
        'total' => 75.50,
    ]);
    Order::factory()->for($bob)->create([
        'delivery_date' => '2026-03-18',
        'payment_status' => PaymentStatus::Paid,
        'total' => 30.00,
    ]);

    $report = (new CustomerReport)->generate(DateRange::forMonth(2026, 3));

    assertMatchesSnapshot($report);
});

test('only active paid orders contribute to customer metrics', function () {
    $customer = Customer::factory()->create();
    $unpaidOnly = Customer::factory()->create();

    Order::factory()->for($customer)->paid()->create([
        'delivery_date' => '2026-03-10',
        'total' => 100.00,
    ]);
    Order::factory()->for($customer)->unpaid()->create([
        'delivery_date' => '2026-03-11',
        'total' => 500.00,
    ]);
    Order::factory()->for($customer)->cancelled()->paid()->create([
        'delivery_date' => '2026-03-12',
        'total' => 700.00,
    ]);
    Order::factory()->for($unpaidOnly)->unpaid()->create([
        'delivery_date' => '2026-03-13',
        'total' => 900.00,
    ]);

    $result = (new CustomerReport)->generate(DateRange::forMonth(2026, 3));

    expect($result['totalCustomersWithOrders'])->toBe(1)
        ->and($result['repeatCustomers'])->toBe(0)
        ->and($result['repeatRate'])->toBe(0.0)
        ->and($result['topCustomers'])->toHaveCount(1)
        ->and($result['topCustomers'][0]['total_spend'])->toBe(100.0)
        ->and($result['topCustomers'][0]['order_count'])->toBe(1);
});
