<?php

use App\DataTransferObjects\Inventory\ProductReportResult;
use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Staff\User;
use App\Reports\Inventory\ProductReport;
use App\ValueObjects\DateRange;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('generates product report for a date range', function () {
    $range = DateRange::forMonth(2026, 3);
    $report = new ProductReport;
    $result = $report->generate($range);

    expect($result)->toBeInstanceOf(ProductReportResult::class)
        ->and($result->products)->toBeEmpty()
        ->and($result->toArray())->toBe(['products' => []]);
});

test('calculates units sold, revenue, and margin for products', function () {
    $user = User::factory()->owner()->create();
    $customer = Customer::factory()->create();

    $product = Product::factory()->create([
        'name' => 'Sourdough',
        'price' => 10.00,
        'cost' => 4.00,
    ]);

    $order = Order::factory()
        ->for($customer)
        ->recycle($user)
        ->delivered()
        ->create(['delivery_date' => '2026-03-15']);

    OrderItem::factory()->recycle($order, $product)->create([
        'quantity' => 5,
        'unit_price' => 10.00,
    ]);

    $range = DateRange::forMonth(2026, 3);
    $report = new ProductReport;
    $result = $report->generate($range);
    $serialized = $result->toArray();

    $sourdough = collect($result->products)->firstWhere('name', 'Sourdough');
    $serializedSourdough = collect($serialized['products'])->firstWhere('name', 'Sourdough');

    expect($sourdough)->not->toBeNull()
        ->and($sourdough['units_sold'])->toBe(5)
        ->and($sourdough['price'])->toEqual(Money::fromDollars(10))
        ->and($sourdough['cost'])->toEqual(Money::fromDollars(4))
        ->and($sourdough['revenue'])->toEqual(Money::fromDollars(50))
        ->and($sourdough['margin'])->toBe(60.0)
        ->and($serializedSourdough['price'])->toBe(10.0)
        ->and($serializedSourdough['cost'])->toBe(4.0)
        ->and($serializedSourdough['revenue'])->toBe(50.0);
});

test('returns null margin when price or cost is zero', function () {
    Product::factory()->create([
        'name' => 'Free Sample',
        'price' => 0,
        'cost' => 0,
    ]);

    $range = DateRange::forMonth(2026, 3);
    $report = new ProductReport;
    $result = $report->generate($range);

    $freeSample = collect($result->products)->firstWhere('name', 'Free Sample');

    expect($freeSample['margin'])->toBeNull();
});
