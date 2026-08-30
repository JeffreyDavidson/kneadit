<?php

use App\Http\Requests\Storefront\StoreOrderRequest;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $category = Category::factory()->create(['name' => 'Bread', 'slug' => 'bread']);
    test()->product = Product::factory()->for($category)->create([
        'name' => 'Sourdough',
        'slug' => 'sourdough',
        'price' => 5.00,
        'is_active' => true,
    ]);
});

test('store order request requires essential fields', function () {
    $request = new StoreOrderRequest;

    foreach (['customer_name', 'customer_email', 'delivery_type', 'delivery_date', 'items'] as $field) {
        $data = validOrderData();
        unset($data[$field]);

        $validator = validator($data, $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has($field))->toBeTrue();
    }
});

test('store order request requires delivery address for delivery orders', function () {
    $request = new StoreOrderRequest;
    $data = array_merge(validOrderData(), [
        'delivery_type' => 'delivery',
        'delivery_address' => null,
    ]);

    $validator = validator($data, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('delivery_address'))->toBeTrue();
});

test('store order request rejects delivery date too soon', function () {
    $request = new StoreOrderRequest;
    $data = array_merge(validOrderData(), [
        'delivery_date' => now()->toDateString(),
    ]);

    $validator = validator($data, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('delivery_date'))->toBeTrue();
});

function validOrderData(): array
{
    return [
        'customer_name' => 'John Doe',
        'customer_email' => 'john@example.com',
        'delivery_type' => 'pickup',
        'delivery_date' => now()->addDays(3)->toDateString(),
        'items' => [
            ['product_id' => 1, 'quantity' => 2],
        ],
    ];
}
