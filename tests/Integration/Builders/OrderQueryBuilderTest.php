<?php

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\ValueObjects\DateRange;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('paid scope returns only paid orders', function () {
    Order::factory()->paid()->create();
    Order::factory()->unpaid()->create();

    $results = Order::query()->paid()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->payment_status)->toBe(PaymentStatus::Paid);
});

test('unpaid scope returns only unpaid orders', function () {
    Order::factory()->unpaid()->create();
    Order::factory()->paid()->create();

    $results = Order::query()->unpaid()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->payment_status)->toBe(PaymentStatus::Unpaid);
});

test('pending scope returns only pending orders', function () {
    Order::factory()->pending()->create();
    Order::factory()->confirmed()->create();

    $results = Order::query()->pending()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->status)->toBe(OrderStatus::Pending);
});

test('ready scope returns only ready orders', function () {
    Order::factory()->ready()->create();
    Order::factory()->pending()->create();

    $results = Order::query()->ready()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->status)->toBe(OrderStatus::Ready);
});

test('active scope excludes cancelled orders', function () {
    Order::factory()->pending()->create();
    Order::factory()->confirmed()->create();
    Order::factory()->cancelled()->create();

    $results = Order::query()->active()->get();

    expect($results)->toHaveCount(2);
});

test('byStatus filters orders by given status', function () {
    Order::factory()->baking()->create();
    Order::factory()->confirmed()->create();

    $results = Order::query()->byStatus(OrderStatus::Baking)->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->status)->toBe(OrderStatus::Baking);
});

test('paidInYear returns paid orders within the given year', function () {
    Order::factory()->paid()->create(['delivery_date' => '2026-06-15']);
    Order::factory()->paid()->create(['delivery_date' => '2025-06-15']);
    Order::factory()->unpaid()->create(['delivery_date' => '2026-06-15']);

    $results = Order::query()->paidInYear(2026)->get();

    expect($results)->toHaveCount(1);
});

test('paidInDateRange returns paid orders within the date range', function () {
    Order::factory()->paid()->create(['delivery_date' => '2026-03-15']);
    Order::factory()->paid()->create(['delivery_date' => '2026-05-01']);
    Order::factory()->unpaid()->create(['delivery_date' => '2026-03-15']);

    $range = DateRange::fromStrings('2026-03-01', '2026-03-31');
    $results = Order::query()->paidInDateRange($range)->get();

    expect($results)->toHaveCount(1);
});

test('inDateRange returns orders within the date range regardless of payment status', function () {
    Order::factory()->paid()->create(['delivery_date' => '2026-03-15']);
    Order::factory()->unpaid()->create(['delivery_date' => '2026-03-20']);
    Order::factory()->create(['delivery_date' => '2026-05-01']);

    $range = DateRange::fromStrings('2026-03-01', '2026-03-31');
    $results = Order::query()->inDateRange($range)->get();

    expect($results)->toHaveCount(2);
});

test('forDeliveryOnDate returns orders with delivery address and active statuses on given date', function () {
    $date = now()->addDays(3);

    Order::factory()->confirmed()->create([
        'delivery_date' => $date,
        'delivery_address' => '123 Main St',
        'delivery_time' => '10:00',
    ]);
    // Excluded: no delivery address
    Order::factory()->confirmed()->create([
        'delivery_date' => $date,
        'delivery_address' => null,
    ]);
    // Excluded: empty delivery address
    Order::factory()->confirmed()->create([
        'delivery_date' => $date,
        'delivery_address' => '',
    ]);
    // Excluded: cancelled status
    Order::factory()->cancelled()->create([
        'delivery_date' => $date,
        'delivery_address' => '456 Oak Ave',
    ]);
    // Excluded: different date
    Order::factory()->confirmed()->create([
        'delivery_date' => now()->addDays(5),
        'delivery_address' => '789 Pine Rd',
    ]);

    $results = Order::query()->forDeliveryOnDate($date)->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->relationLoaded('customer'))->toBeTrue()
        ->and($results->first()->relationLoaded('orderItems'))->toBeTrue();
});

test('forCustomerEmail returns orders for the matching customer email', function () {
    $customer = Customer::factory()->create(['email' => 'baker@example.com']);
    $otherCustomer = Customer::factory()->create(['email' => 'other@example.com']);

    Order::factory()->recycle($customer)->create();
    Order::factory()->recycle($otherCustomer)->create();

    $results = Order::query()->forCustomerEmail('baker@example.com')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->relationLoaded('customer'))->toBeTrue()
        ->and($results->first()->relationLoaded('orderItems'))->toBeTrue();
});
