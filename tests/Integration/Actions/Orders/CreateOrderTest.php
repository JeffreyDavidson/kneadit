<?php

use App\Actions\Orders\CreateOrder;
use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\OrderStatus;
use App\Exceptions\Orders\InsufficientStockException;
use App\Mail\Orders\OrderPlacedMail;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('creates order with correct totals and items', function () {
    $product = Product::factory()->create(['price' => 12.50]);

    $order = resolve(CreateOrder::class)(
        CreateOrderData::fromArray([
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_date' => now()->addDays(5)->toDateString(),
            'delivery_type' => DeliveryType::Pickup->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])
    );

    expect($order)
        ->not->toBeNull()
        ->status->toBe(OrderStatus::Pending)
        ->and($order->subtotal->dollars())->toBe(25.00)
        ->and($order->total->dollars())->toBe(25.00);

    $order->load('orderItems', 'customer');
    expect($order->orderItems)->toHaveCount(1)->and($order->customer->email)->toBe('jane@example.com');
});

test('returns null when capacity is full', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $deliveryDate = now()->addDays(5)->toDateString();

    settings(['default_daily_capacity' => '1']);
    Order::factory()->confirmed()->create(['delivery_date' => $deliveryDate]);

    $order = resolve(CreateOrder::class)(
        CreateOrderData::fromArray([
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_date' => $deliveryDate,
            'delivery_type' => DeliveryType::Pickup->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])
    );

    expect($order)->toBeNull();
});

test('persists tip_amount and includes it in total', function () {
    $product = Product::factory()->create(['price' => 20.00]);

    $order = resolve(CreateOrder::class)(
        CreateOrderData::fromArray([
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_date' => now()->addDays(5)->toDateString(),
            'delivery_type' => DeliveryType::Pickup->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            'tip_amount' => 4.50,
        ])
    );

    expect($order)->not->toBeNull()
        ->and($order->subtotal->dollars())->toBe(20.00)
        ->and($order->tip_amount->dollars())->toBe(4.50)
        ->and($order->total->dollars())->toBe(24.50);
});

test('throws InsufficientStockException when projected ingredient draw exceeds stock', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 5.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 2.0, 'unit' => 'lb']);

    expect(fn () => resolve(CreateOrder::class)(
        CreateOrderData::fromArray([
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_date' => now()->addDays(5)->toDateString(),
            'delivery_type' => DeliveryType::Pickup->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ])
    ))->toThrow(InsufficientStockException::class, 'Flour');
});

test('rolls back the entire order pipeline when stock check fails', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->for($product)->create();
    $sugar = Ingredient::factory()->create(['name' => 'Sugar', 'current_stock' => 1.00]);
    $recipe->inventoryIngredients()->attach($sugar->id, ['quantity' => 1.0, 'unit' => 'lb']);

    $orderCountBefore = Order::query()->count();

    try {
        resolve(CreateOrder::class)(
            CreateOrderData::fromArray([
                'customer_name' => 'Jane Doe',
                'customer_email' => 'jane@example.com',
                'delivery_date' => now()->addDays(5)->toDateString(),
                'delivery_type' => DeliveryType::Pickup->value,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 5],
                ],
            ])
        );
    } catch (InsufficientStockException) {
        // expected
    }

    expect(Order::query()->count())->toBe($orderCountBefore);
});

test('allows placement when stock is sufficient', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $recipe = Recipe::factory()->for($product)->create();
    $butter = Ingredient::factory()->create(['name' => 'Butter', 'current_stock' => 100.00]);
    $recipe->inventoryIngredients()->attach($butter->id, ['quantity' => 1.0, 'unit' => 'lb']);

    $order = resolve(CreateOrder::class)(
        CreateOrderData::fromArray([
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_date' => now()->addDays(5)->toDateString(),
            'delivery_type' => DeliveryType::Pickup->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ])
    );

    expect($order)->not->toBeNull();
});

test('sends order placed email to customer on creation', function () {
    $product = Product::factory()->create(['price' => 10.00]);

    resolve(CreateOrder::class)(
        CreateOrderData::fromArray([
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_date' => now()->addDays(5)->toDateString(),
            'delivery_type' => DeliveryType::Pickup->value,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])
    );

    Mail::assertQueued(OrderPlacedMail::class);
});
