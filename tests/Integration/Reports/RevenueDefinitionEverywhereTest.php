<?php

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Queries\Customers\CustomerDirectoryStatsQuery;
use App\Queries\Reporting\WeeklyDigestQuery;
use App\Reports\Customers\CustomerReport;
use App\Reports\Inventory\ProductReport;
use App\ValueObjects\DateRange;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    setUpTenantTest();
    Date::setTestNow('2026-09-16 12:00');

    test()->customer = Customer::factory()->create();
    test()->product = Product::factory()->create(['name' => 'Sourdough', 'price' => 10.00, 'cost' => 4.00]);

    // One customer: a revenue order (paid, not cancelled), then the orders that must never count.
    $orders = [
        [['total' => 100, 'payment_status' => PaymentStatus::Paid], 2],
        [['total' => 70, 'payment_status' => PaymentStatus::Refunded], 5],
        [['total' => 30, 'payment_status' => PaymentStatus::Paid, 'status' => OrderStatus::Cancelled], 7],
        [['total' => 20, 'payment_status' => PaymentStatus::Unpaid], 11],
    ];

    foreach ($orders as [$attributes, $quantity]) {
        $order = Order::factory()->for(test()->customer)->create([
            ...$attributes,
            'delivery_date' => '2026-09-10',
            'created_at' => '2026-09-01 12:00:00',
        ]);

        OrderItem::factory()->recycle($order, test()->product)->create(['quantity' => $quantity, 'unit_price' => 10.00]);
    }

    test()->range = DateRange::fromStrings('2026-09-07', '2026-09-13');
});

test('the product report counts only revenue orders', function () {
    $product = resolve(ProductReport::class)->generate(test()->range)->products[0];

    expect($product->unitsSold)->toBe(2)
        ->and($product->revenue)->toEqual(Money::fromDollars(20));
});

test('the customer report counts only revenue orders', function () {
    $report = resolve(CustomerReport::class)->generate(test()->range);

    expect($report->totalCustomersWithOrders)->toBe(1)
        ->and($report->repeatCustomers)->toBe(0)
        ->and($report->topCustomers[0]->totalSpend)->toEqual(Money::fromDollars(100))
        ->and($report->topCustomers[0]->orderCount)->toBe(1);
});

test('customer lifetime value and RFM metrics count only revenue orders', function () {
    $lifetime = Customer::query()->withOrderMetrics()->findOrFail(test()->customer->id);
    $rfm = Customer::query()->withRfmMetrics()->findOrFail(test()->customer->id);
    $range = Customer::query()->withPaidOrderMetrics(test()->range)->findOrFail(test()->customer->id);

    expect((int) $lifetime->orders_sum_total)->toBe(10000)
        ->and($lifetime->revenue_orders_count)->toBe(1)
        ->and((int) $rfm->monetary_cents)->toBe(10000)
        ->and($rfm->frequency)->toBe(1)
        ->and((int) $range->total_spend)->toBe(10000)
        ->and($range->order_count)->toBe(1);
});

test('the customer directory stats count only revenue orders', function () {
    $stats = CustomerDirectoryStatsQuery::get();

    expect($stats['avg_lifetime_value'])->toBe('$100.00')
        ->and($stats['top_customer_value'])->toBe('$100.00');
});

test('the weekly digest ranks products by revenue orders delivered in the week', function () {
    $top = WeeklyDigestQuery::topProducts(test()->range);

    expect($top)->toHaveCount(1)
        ->and((int) $top->first()->total_qty)->toBe(2);
});
