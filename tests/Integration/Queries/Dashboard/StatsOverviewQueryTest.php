<?php

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Engagement\PageView;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Queries\Dashboard\StatsOverviewQuery;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    setUpTenantTest();
    Date::setTestNow('2026-08-12 12:00:00');
});

afterEach(fn () => Date::setTestNow());

test('returns seven-day charts and weekly revenue from grouped aggregates', function () {
    Order::factory()->create([
        'delivery_date' => Date::today(),
        'status' => OrderStatus::Pending,
        'payment_status' => PaymentStatus::Unpaid,
        'total' => 10,
        'created_at' => Date::now(),
    ]);
    Order::factory()->create([
        'delivery_date' => Date::today(),
        'status' => OrderStatus::Confirmed,
        'payment_status' => PaymentStatus::Paid,
        'total' => 20,
        'created_at' => Date::now()->subDay(),
    ]);
    Order::factory()->create([
        'delivery_date' => Date::today()->subWeek(),
        'status' => OrderStatus::Confirmed,
        'payment_status' => PaymentStatus::Paid,
        'total' => 30,
        'created_at' => Date::now()->subWeek(),
    ]);
    PageView::factory()->count(2)->create([
        'product_id' => null,
        'created_at' => Date::now(),
    ]);
    PageView::factory()->create([
        'product_id' => Product::factory(),
        'created_at' => Date::now(),
    ]);

    $data = resolve(StatsOverviewQuery::class)->get();
    expect($data)->toMatchArray(['ordersChart' => [0, 0, 0, 0, 0, 0, 2], 'todaysOrders' => 2]);
});

test('loads the complete dashboard dataset in five queries', function () {
    DB::flushQueryLog();
    DB::enableQueryLog();

    resolve(StatsOverviewQuery::class)->get();

    expect(DB::getQueryLog())->toHaveCount(5);
});
