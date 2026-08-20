<?php

use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Staff\User;
use App\Reports\Inventory\ProductReport;
use App\ValueObjects\DateRange;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

/**
 * @param array<string, mixed> $report
 * @return array{units_sold: mixed, revenue: mixed, margin: mixed}
 */
function reportedProduct(array $report, string $name): array
{
    $products = data_get($report, 'products', []);
    throw_unless(is_array($products), RuntimeException::class, 'Expected report products to be an array.');

    foreach ($products as $product) {
        if (is_array($product) && data_get($product, 'name') === $name) {
            return [
                'units_sold' => data_get($product, 'units_sold'),
                'revenue' => data_get($product, 'revenue'),
                'margin' => data_get($product, 'margin'),
            ];
        }
    }

    throw new RuntimeException("Product {$name} was not found in the report.");
}

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

    OrderItem::factory()->recycle($order, $product)->create([
        'quantity' => 5,
        'unit_price' => 10.00,
    ]);

    $range = DateRange::forMonth(2026, 3);
    $report = new ProductReport;
    $result = $report->generate($range);

    $sourdough = reportedProduct($result, 'Sourdough');

    expect($sourdough['units_sold'])->toBe(5)
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

    $freeSample = reportedProduct($result, 'Free Sample');

    expect($freeSample['margin'])->toBeNull();
});
