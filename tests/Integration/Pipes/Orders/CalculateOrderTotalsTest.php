<?php

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Orders\DeliveryType;
use App\Exceptions\Orders\NoOrderableItemsException;
use App\Models\Inventory\Product;
use App\Pipes\Orders\CalculateOrderTotals;
use App\Pipes\Orders\OrderPipelineData;
use App\Services\Settings\TenantSettings;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('calculates subtotal and total for active products', function () {
    $product = Product::factory()->create(['price' => 10.00, 'is_active' => true]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => $product->id, 'quantity' => 3]],
    );

    $payload = new OrderPipelineData($data);
    $pipe = resolve(CalculateOrderTotals::class);

    $result = $pipe->handle($payload, fn ($p) => $p);

    expect($result->subtotal->dollars())->toBe(30.0)
        ->and($result->total->dollars())->toBe(30.0)
        ->and($result->orderItems)->toHaveCount(1)
        ->and($result->cancelled)->toBeFalse();
});

test('loads only product fields needed for order totals', function () {
    $product = Product::factory()->create(['price' => 10.00, 'is_active' => true]);
    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => $product->id, 'quantity' => 2]],
    );
    $productQueries = [];

    DB::listen(function (QueryExecuted $query) use (&$productQueries): void {
        $sql = strtolower(str_replace(['"', '`', '[', ']'], '', $query->sql));

        if (str_contains($sql, 'from products')) {
            $productQueries[] = $sql;
        }
    });

    $result = resolve(CalculateOrderTotals::class)->handle(new OrderPipelineData($data), fn ($payload) => $payload);

    expect($result->subtotal->dollars())->toBe(20.0)
        ->and($productQueries)->toHaveCount(1);

    $selectClause = explode(' from products', $productQueries[0], 2)[0];

    expect($selectClause)->toBe('select id, is_active, price');
});

test('skips inactive products', function () {
    $active = Product::factory()->create(['price' => 10.00, 'is_active' => true]);
    $inactive = Product::factory()->create(['price' => 5.00, 'is_active' => false]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [
            ['product_id' => $active->id, 'quantity' => 1],
            ['product_id' => $inactive->id, 'quantity' => 2],
        ],
    );

    $payload = new OrderPipelineData($data);
    $pipe = resolve(CalculateOrderTotals::class);

    $result = $pipe->handle($payload, fn ($p) => $p);

    expect($result->subtotal->dollars())->toBe(10.0)
        ->and($result->orderItems)->toHaveCount(1);
});

test('rejects the order when no valid items exist', function () {
    $product = Product::factory()->create(['price' => 5.00, 'is_active' => false]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => $product->id, 'quantity' => 1]],
    );

    $payload = new OrderPipelineData($data);
    $pipe = resolve(CalculateOrderTotals::class);

    expect(fn () => $pipe->handle($payload, fn ($p) => $p))->toThrow(NoOrderableItemsException::class)
        ->and($payload->orderItems)->toBeEmpty();
});

test('charges the bakery delivery tier fee unless the order reaches the free-delivery minimum', function (float $price, string $tier, string $freeDeliveryMinimum, float $fee) {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings([
        'deliveryFeeTiers' => [
            ['min_distance' => 0, 'max_distance' => 5, 'fee' => 3.00, 'description' => 'Local'],
            ['min_distance' => 5, 'max_distance' => 10, 'fee' => 6.50, 'description' => 'Nearby'],
        ],
        'freeDeliveryMinimum' => $freeDeliveryMinimum,
    ])));
    $product = Product::factory()->create(['price' => $price, 'is_active' => true]);
    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Delivery->value,
        items: [['product_id' => $product->id, 'quantity' => 1]],
        deliveryTier: $tier,
    );

    $result = resolve(CalculateOrderTotals::class)->handle(new OrderPipelineData($data), fn ($p) => $p);

    expect($result->deliveryFee->dollars())->toBe($fee)
        ->and($result->total->dollars())->toBe($price + $fee);
})->with([
    'first tier below the minimum' => [20.00, '0', '50', 3.00],
    'second tier below the minimum' => [20.00, '1', '50', 6.50],
    'at the free-delivery minimum' => [50.00, '1', '50', 0.00],
    'over the free-delivery minimum' => [60.00, '0', '50', 0.00],
    'no free-delivery minimum set' => [60.00, '1', '0', 6.50],
]);

test('does not add delivery fee for pickup orders', function () {
    $product = Product::factory()->create(['price' => 20.00, 'is_active' => true]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => $product->id, 'quantity' => 1]],
    );

    $payload = new OrderPipelineData($data);
    $pipe = resolve(CalculateOrderTotals::class);

    $result = $pipe->handle($payload, fn ($p) => $p);

    expect($result->deliveryFee->dollars())->toBe(0.0)
        ->and($result->total->dollars())->toBe(20.0);
});

test('adds tip to total when tipAmount is provided', function () {
    $product = Product::factory()->create(['price' => 20.00, 'is_active' => true]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => $product->id, 'quantity' => 1]],
        tipAmount: 4.0,
    );

    $payload = new OrderPipelineData($data);
    $pipe = resolve(CalculateOrderTotals::class);

    $result = $pipe->handle($payload, fn ($p) => $p);

    expect($result->tipAmount->dollars())->toBe(4.0)
        ->and($result->total->dollars())->toBe(24.0);
});

test('clamps negative tipAmount to zero', function () {
    $product = Product::factory()->create(['price' => 20.00, 'is_active' => true]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => $product->id, 'quantity' => 1]],
        tipAmount: -5.0,
    );

    $payload = new OrderPipelineData($data);
    $pipe = resolve(CalculateOrderTotals::class);

    $result = $pipe->handle($payload, fn ($p) => $p);

    expect($result->tipAmount->dollars())->toBe(0.0)
        ->and($result->total->dollars())->toBe(20.0);
});

test('tip stacks with delivery fee in total', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings([
        'deliveryFeeTiers' => [['min_distance' => 0, 'max_distance' => 5, 'fee' => 5.00, 'description' => 'Local']],
        'freeDeliveryMinimum' => '0',
    ])));

    $product = Product::factory()->create(['price' => 20.00, 'is_active' => true]);

    $data = new CreateOrderData(
        customerName: 'Jane',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Delivery->value,
        items: [['product_id' => $product->id, 'quantity' => 1]],
        deliveryTier: '0',
        tipAmount: 3.0,
    );

    $payload = new OrderPipelineData($data);
    $pipe = resolve(CalculateOrderTotals::class);

    $result = $pipe->handle($payload, fn ($p) => $p);

    expect($result->subtotal->dollars())->toBe(20.0)
        ->and($result->deliveryFee->dollars())->toBe(5.0)
        ->and($result->tipAmount->dollars())->toBe(3.0)
        ->and($result->total->dollars())->toBe(28.0);
});
