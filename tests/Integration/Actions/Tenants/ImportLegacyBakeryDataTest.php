<?php

use App\Actions\Tenants\ImportLegacyBakeryData;

beforeEach(function () {
    setUpTenantTest();
});

it('imports a legacy catalog and order history idempotently while converting dollars to cents', function () {
    $data = [
        'categories' => [['id' => 10, 'name' => 'Breads', 'description' => 'Fresh bread', 'sort_order' => 1]],
        'products' => [['id' => 20, 'category_id' => 10, 'name' => 'Sourdough', 'description' => 'A loaf', 'price' => '12.50', 'cost' => '3.25', 'is_available' => true]],
        'orders' => [[
            'id' => 30,
            'order_number' => 'BOB-TEST',
            'customer_name' => 'Jane Baker',
            'customer_email' => 'Jane@Example.com',
            'customer_phone' => '5551234567',
            'fulfillment_type' => 'pickup',
            'requested_date' => '2026-08-15',
            'requested_time' => '10:00',
            'subtotal' => '25.00',
            'delivery_fee' => '0.00',
            'discount_amount' => '2.50',
            'total' => '22.50',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]],
        'order_items' => [['id' => 40, 'order_id' => 30, 'product_id' => 20, 'product_name' => 'Sourdough', 'unit_price' => '12.50', 'quantity' => 2]],
        'reviews' => [['id' => 50, 'name' => 'Jane Baker', 'email' => 'jane@example.com', 'rating' => 5, 'body' => 'Excellent', 'status' => 'approved', 'is_featured' => true]],
        'settings' => [['key' => 'business_name', 'value' => 'Bakery on Biscotto'], ['key' => 'tagline', 'value' => 'Freshly baked with love']],
    ];

    $import = resolve(ImportLegacyBakeryData::class);
    $firstResult = $import($data);
    $secondResult = $import($data);

    expect($firstResult)->toMatchArray(['categories' => 1, 'products' => 1, 'customers' => 1, 'orders' => 1, 'order_items' => 1, 'reviews' => 1, 'settings' => 2])
        ->and($secondResult)->toEqual($firstResult);

    test()->assertDatabaseCount('categories', 1)
        ->assertDatabaseCount('products', 1)
        ->assertDatabaseCount('customers', 1)
        ->assertDatabaseCount('orders', 1)
        ->assertDatabaseCount('order_items', 1)
        ->assertDatabaseCount('reviews', 1)
        ->assertDatabaseHas('products', ['slug' => 'sourdough', 'price' => 1250, 'cost' => 325])
        ->assertDatabaseHas('customers', ['email' => 'jane@example.com'])
        ->assertDatabaseHas('orders', ['order_number' => 'BOB-TEST', 'subtotal' => 2500, 'discount_amount' => 250, 'total' => 2250])
        ->assertDatabaseHas('order_items', ['name' => 'Sourdough', 'unit_price' => 1250])
        ->assertDatabaseHas('settings', ['key' => 'store_name', 'value' => 'Bakery on Biscotto'])
        ->assertDatabaseHas('settings', ['key' => 'store_tagline', 'value' => 'Freshly baked with love'])
        ->assertDatabaseHas('settings', ['key' => 'storefront_theme', 'value' => 'classic'])
        ->assertDatabaseHas('settings', ['key' => 'admin_theme', 'value' => 'honey']);
});
