<?php

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\Order;
use App\Queries\Financial\RevenueQuery;
use App\ValueObjects\DateRange;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('total returns revenue for date range', function () {
    Order::factory()->create(['delivery_date' => now(), 'total' => 50, 'status' => OrderStatus::Confirmed]);
    Order::factory()->create(['delivery_date' => now(), 'total' => 30, 'status' => OrderStatus::Confirmed]);
    Order::factory()->create(['delivery_date' => now()->subMonth(), 'total' => 100, 'status' => OrderStatus::Confirmed]);

    $range = [now()->startOfWeek(), now()->endOfWeek()];

    expect(RevenueQuery::total($range))->toBe(80.0);
});

test('orderCount returns number of active orders in date range', function () {
    Order::factory()->create(['delivery_date' => now(), 'status' => OrderStatus::Confirmed]);
    Order::factory()->create(['delivery_date' => now(), 'status' => OrderStatus::Pending]);
    Order::factory()->create(['delivery_date' => now(), 'status' => OrderStatus::Delivered]);

    $range = [now()->startOfWeek(), now()->endOfWeek()];

    expect(RevenueQuery::orderCount($range))->toBe(3);
});

test('orderCount excludes cancelled orders', function () {
    Order::factory()->create(['delivery_date' => now(), 'status' => OrderStatus::Confirmed]);
    Order::factory()->cancelled()->create(['delivery_date' => now()]);

    $range = [now()->startOfWeek(), now()->endOfWeek()];

    expect(RevenueQuery::orderCount($range))->toBe(1);
});

test('orderCount excludes orders outside date range', function () {
    Order::factory()->create(['delivery_date' => now(), 'status' => OrderStatus::Confirmed]);
    Order::factory()->create(['delivery_date' => now()->subMonth(), 'status' => OrderStatus::Confirmed]);

    $range = [now()->startOfWeek(), now()->endOfWeek()];

    expect(RevenueQuery::orderCount($range))->toBe(1);
});

test('orderCount accepts DateRange value object', function () {
    Order::factory()->create(['delivery_date' => now(), 'status' => OrderStatus::Confirmed]);
    Order::factory()->create(['delivery_date' => now(), 'status' => OrderStatus::Pending]);

    $range = new DateRange(now()->subDay(), now()->addDay());

    expect(RevenueQuery::orderCount($range))->toBe(2);
});
