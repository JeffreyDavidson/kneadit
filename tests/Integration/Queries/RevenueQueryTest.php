<?php

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\Order;
use App\Queries\Financial\RevenueQuery;
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
