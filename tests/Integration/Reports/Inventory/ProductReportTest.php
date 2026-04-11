<?php

use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Reports\Inventory\ProductReport;
use App\ValueObjects\DateRange;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('generates product report for a date range', function () {
    $range = DateRange::forMonth(2026, 3);
    $report = new ProductReport;
    $result = $report->generate($range);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('products');
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

    $order->orderItems()->create([
        'product_id' => $product->id,
        'quantity' => 5,
        'unit_price' => 10.00,
    ]);

    $range = DateRange::forMonth(2026, 3);
    $report = new ProductReport;
    $result = $report->generate($range);

    $sourdough = collect($result['products'])->firstWhere('name', 'Sourdough');

    expect($sourdough)->not->toBeNull()
        ->and($sourdough['units_sold'])->toBe(5)
        ->and($sourdough['revenue'])->toBe(50.0)
        ->and($sourdough['margin'])->toBe(60.0);
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

    $freeSample = collect($result['products'])->firstWhere('name', 'Free Sample');

    expect($freeSample['margin'])->toBeNull();
});
