<?php

use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Reports\Orders\SalesReport;
use App\ValueObjects\DateRange;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

it('returns expected report keys', function () {
    $report = new SalesReport;
    $range = DateRange::fromStrings('2026-01-01', '2026-01-31');

    $result = $report->generate($range);

    expect($result)->toHaveKeys(['totalOrders', 'totalRevenue', 'avgOrderValue', 'ordersByStatus', 'topProducts', 'revenueByDay']);
});

it('only includes active paid orders in sales metrics', function () {
    $product = Product::factory()->create();

    $paidOrder = Order::factory()->paid()->create([
        'delivery_date' => '2026-01-15',
        'total' => 20.00,
    ]);
    $unpaidOrder = Order::factory()->unpaid()->create([
        'delivery_date' => '2026-01-16',
        'total' => 500.00,
    ]);
    $cancelledOrder = Order::factory()->cancelled()->paid()->create([
        'delivery_date' => '2026-01-17',
        'total' => 700.00,
    ]);

    OrderItem::factory()->recycle($paidOrder, $product)->create(['quantity' => 2, 'unit_price' => 10.00]);
    OrderItem::factory()->recycle($unpaidOrder, $product)->create(['quantity' => 50, 'unit_price' => 10.00]);
    OrderItem::factory()->recycle($cancelledOrder, $product)->create(['quantity' => 70, 'unit_price' => 10.00]);

    $result = (new SalesReport)->generate(DateRange::fromStrings('2026-01-01', '2026-01-31'));

    expect($result['totalOrders'])->toBe(1)
        ->and($result['totalRevenue'])->toBe(20.0)
        ->and($result['avgOrderValue'])->toBe(20.0)
        ->and($result['ordersByStatus'])->toBe(['pending' => 1])
        ->and($result['topProducts'])->toHaveCount(1)
        ->and($result['topProducts'][0]['units_sold'])->toBe(2)
        ->and($result['revenueByDay'])->toBe([['date' => '2026-01-15', 'revenue' => 20]]);
});
