<?php

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\Order;
use App\Queries\Financial\RevenueQuery;
use App\ValueObjects\DateRange;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('total returns revenue for date range', function () {
    Order::factory()->paid()->create(['delivery_date' => now(), 'total' => 50, 'status' => OrderStatus::Confirmed]);
    Order::factory()->paid()->withDeliveryDate(now())->confirmed()->create(['total' => 30]);
    Order::factory()->paid()->withDeliveryDate(now()->subMonth())->confirmed()->create(['total' => 100]);

    $range = [now()->startOfWeek(), now()->endOfWeek()];

    expect(RevenueQuery::total($range))->toEqual(Money::fromDollars(80));
});

test('orderCount returns number of active orders in date range', function () {
    Order::factory()->withDeliveryDate(now())->confirmed()->create();
    Order::factory()->withDeliveryDate(now())->pending()->create();
    Order::factory()->withDeliveryDate(now())->create(['status' => OrderStatus::Delivered]);

    $range = [now()->startOfWeek(), now()->endOfWeek()];

    expect(RevenueQuery::orderCount($range))->toBe(3);
});

test('orderCount excludes cancelled orders', function () {
    Order::factory()->withDeliveryDate(now())->confirmed()->create();
    Order::factory()->cancelled()->withDeliveryDate(now())->create();

    $range = [now()->startOfWeek(), now()->endOfWeek()];

    expect(RevenueQuery::orderCount($range))->toBe(1);
});

test('orderCount excludes orders outside date range', function () {
    Order::factory()->withDeliveryDate(now())->confirmed()->create();
    Order::factory()->withDeliveryDate(now()->subMonth())->confirmed()->create();

    $range = [now()->startOfWeek(), now()->endOfWeek()];

    expect(RevenueQuery::orderCount($range))->toBe(1);
});

test('orderCount accepts DateRange value object', function () {
    Order::factory()->withDeliveryDate(now())->confirmed()->create();
    Order::factory()->withDeliveryDate(now())->pending()->create();

    $range = new DateRange(now()->subDay(), now()->addDay());

    expect(RevenueQuery::orderCount($range))->toBe(2);
});
