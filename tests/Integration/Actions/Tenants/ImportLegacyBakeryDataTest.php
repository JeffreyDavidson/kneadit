<?php

use App\Actions\Tenants\ImportLegacyBakeryAssets;
use App\Actions\Tenants\ImportLegacyBakeryData;
use App\Services\Settings\TenantSettingCipher;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

beforeEach(function () {
    config(['app.key' => 'base64:BskavyAdxjag1K/BGSEfPPiwB/QDha6hMH4H0i1wM7A=']);
    setUpTenantTest();
});

it('imports a legacy catalog and order history idempotently while converting dollars to cents', function () {
    $data = [
        'categories' => [['id' => 10, 'name' => 'Breads', 'description' => 'Fresh bread', 'sort_order' => 1]],
        'products' => [['id' => 20, 'category_id' => 10, 'name' => 'Sourdough', 'description' => 'A loaf', 'price' => '12.50', 'cost' => '3.25', 'is_available' => true]],
        'coupons' => [['id' => 25, 'code' => 'welcome5', 'type' => 'fixed_amount', 'value' => '5.00', 'minimum_order' => '20.00', 'times_used' => 2]],
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
            'coupon_id' => 25,
        ]],
        'order_notes' => [
            ['id' => 42, 'order_id' => 30, 'type' => 'system', 'content' => 'Email notification sent: Order Confirmed', 'created_at' => '2026-08-15 09:15:00'],
            ['id' => 41, 'order_id' => 30, 'type' => 'status_change', 'content' => 'Status changed from Pending to Confirmed', 'created_at' => '2026-08-15 09:00:00'],
        ],
        'order_items' => [['id' => 40, 'order_id' => 30, 'product_id' => 20, 'product_name' => 'Sourdough', 'unit_price' => '12.50', 'quantity' => 2]],
        'reviews' => [['id' => 50, 'name' => 'Jane Baker', 'email' => 'jane@example.com', 'rating' => 5, 'body' => 'Excellent', 'status' => 'approved', 'is_featured' => true]],
        'recipes' => [['id' => 60, 'product_id' => 20, 'name' => 'Sourdough Recipe', 'prep_time_minutes' => 30]],
        'recipe_ingredients' => [['id' => 61, 'recipe_id' => 60, 'name' => 'Flour', 'quantity' => 2, 'unit' => 'lb', 'cost_per_unit' => '1.50']],
        'recipe_stages' => [['id' => 62, 'recipe_id' => 60, 'name' => 'Mix', 'instructions' => 'Combine ingredients.', 'sort_order' => 1]],
        'expenses' => [['id' => 70, 'description' => 'Fuel', 'amount' => '20.00', 'category' => 'delivery_gas', 'date' => '2026-08-01', 'business_percentage' => 50]],
        'incomes' => [['id' => 71, 'description' => 'Custom cake', 'amount' => '75.00', 'source' => 'custom_order', 'date' => '2026-08-02']],
        'capacity_limits' => [['id' => 80, 'day_of_week' => 1, 'specific_date' => null, 'max_orders' => 10, 'is_blocked' => false]],
        'holidays' => [['id' => 81, 'name' => 'Labor Day', 'date' => '2026-09-07', 'is_active' => true]],
        'contact_messages' => [['id' => 90, 'name' => 'Jane Baker', 'email' => 'jane@example.com', 'subject' => 'Question', 'message' => 'Are you open?', 'status' => 'replied']],
        'waitlist_entries' => [['id' => 91, 'customer_name' => 'Jane Baker', 'customer_email' => 'jane@example.com', 'requested_date' => '2026-08-20', 'product_interest' => 'Sourdough', 'status' => 'waiting']],
        'customer_favorites' => [['id' => 92, 'customer_email' => 'jane@example.com', 'product_id' => 20]],
        'settings' => [
            ['key' => 'business_name', 'value' => 'Bakery on Biscotto'],
            ['key' => 'tagline', 'value' => 'Freshly baked with love'],
            ['key' => 'delivery_fee_tiers', 'value' => '0-5:5.00,5-10:8.00,10+:12.00'],
            ['key' => 'operating_hours', 'value' => "Mon-Fri: 7am - 6pm\nSat: 8am - 4pm\nSun: Closed"],
            ['key' => 'paypal_client_id', 'value' => 'legacy-client-id'],
            ['key' => 'paypal_client_secret', 'value' => 'legacy-client-secret'],
        ],
    ];

    $import = resolve(ImportLegacyBakeryData::class);
    $firstResult = $import($data);
    $secondResult = $import($data);

    expect($firstResult)->toMatchArray(['categories' => 1, 'products' => 1, 'customers' => 1, 'orders' => 1, 'order_notes' => 2, 'order_items' => 1, 'reviews' => 1, 'settings' => 6])
        ->and($secondResult)->toEqual($firstResult);

    test()->assertDatabaseCount('categories', 1)
        ->assertDatabaseCount('products', 1)
        ->assertDatabaseCount('customers', 1)
        ->assertDatabaseCount('orders', 1)
        ->assertDatabaseCount('order_items', 1)
        ->assertDatabaseCount('reviews', 1)
        ->assertDatabaseCount('recipes', 1)
        ->assertDatabaseCount('expenses', 1)
        ->assertDatabaseCount('incomes', 1)
        ->assertDatabaseCount('coupons', 1)
        ->assertDatabaseCount('capacity_limits', 1)
        ->assertDatabaseCount('holidays', 1)
        ->assertDatabaseCount('contact_messages', 1)
        ->assertDatabaseCount('waitlist_entries', 1)
        ->assertDatabaseCount('customer_favorites', 1)
        ->assertDatabaseHas('products', ['slug' => 'sourdough', 'price' => 1250, 'cost' => 325])
        ->assertDatabaseHas('customers', ['email' => 'jane@example.com'])
        ->assertDatabaseHas('orders', ['order_number' => 'BOB-TEST', 'subtotal' => 2500, 'discount_amount' => 250, 'total' => 2250, 'coupon_id' => 1])
        ->assertDatabaseHas('order_items', ['name' => 'Sourdough', 'unit_price' => 1250])
        ->assertDatabaseHas('coupons', ['code' => 'WELCOME5', 'type' => 'fixed', 'fixed_amount' => 500, 'min_order_amount' => 2000])
        ->assertDatabaseHas('recipes', ['name' => 'Sourdough Recipe', 'cost' => 300])
        ->assertDatabaseHas('expenses', ['description' => 'Fuel', 'amount' => 2000, 'category' => 'delivery', 'deductible_amount' => 1000])
        ->assertDatabaseHas('incomes', ['description' => 'Custom cake', 'amount' => 7500, 'source' => 'other'])
        ->assertDatabaseHas('holidays', ['name' => 'Labor Day', 'date' => '2026-09-07'])
        ->assertDatabaseHas('contact_messages', ['email' => 'jane@example.com', 'is_read' => true])
        ->assertDatabaseHas('customer_favorites', ['customer_email' => 'jane@example.com'])
        ->assertDatabaseHas('settings', ['key' => 'store_name', 'value' => 'Bakery on Biscotto'])
        ->assertDatabaseHas('settings', ['key' => 'store_tagline', 'value' => 'Freshly baked with love'])
        ->assertDatabaseHas('settings', ['key' => 'storefront_theme', 'value' => 'biscotto'])
        ->assertDatabaseHas('settings', ['key' => 'admin_theme', 'value' => 'honey']);

    $deliveryFeeTiers = json_decode((string) DB::table('settings')->where('key', 'delivery_fee_tiers')->value('value'), true);
    $operatingHours = json_decode((string) DB::table('settings')->where('key', 'operating_hours')->value('value'), true);
    $paypalClientId = DB::table('settings')->where('key', 'paypal_client_id')->value('value');
    $paypalClientSecret = DB::table('settings')->where('key', 'paypal_client_secret')->value('value');
    $orderNotes = DB::table('orders')->where('order_number', 'BOB-TEST')->value('notes');

    expect($deliveryFeeTiers)->toHaveCount(3)
        ->and($deliveryFeeTiers[0])->toMatchArray(['min_distance' => 0, 'max_distance' => 5, 'fee' => '5.00'])
        ->and($operatingHours['monday'])->toBe(['open' => '07:00', 'close' => '18:00'])
        ->and($operatingHours['sunday'])->toBe([])
        ->and($paypalClientId)->not->toBe('legacy-client-id')
        ->and($paypalClientSecret)->not->toBe('legacy-client-secret')
        ->and(resolve(TenantSettingCipher::class)->decrypt('paypal_client_id', $paypalClientId))->toBe('legacy-client-id')
        ->and(resolve(TenantSettingCipher::class)->decrypt('paypal_client_secret', $paypalClientSecret))->toBe('legacy-client-secret')
        ->and($orderNotes)->toBe("Legacy order history:\n[2026-08-15 09:00:00] [Status Change] Status changed from Pending to Confirmed\n[2026-08-15 09:15:00] [System] Email notification sent: Order Confirmed");
});

it('rejects missing foreign-key references before writing any records', function () {
    $import = resolve(ImportLegacyBakeryData::class);

    expect(fn () => $import([
        'categories' => [['id' => 10, 'name' => 'Breads']],
        'products' => [['id' => 20, 'category_id' => 999, 'name' => 'Sourdough', 'price' => '12.50']],
    ]))->toThrow(InvalidArgumentException::class, 'Product at index 0 references missing category ID 999.');

    test()->assertDatabaseCount('categories', 0)
        ->assertDatabaseCount('products', 0);
});

it('rejects duplicate legacy IDs before writing any records', function () {
    $import = resolve(ImportLegacyBakeryData::class);

    expect(fn () => $import([
        'categories' => [
            ['id' => 10, 'name' => 'Breads'],
            ['id' => 10, 'name' => 'Pastries'],
        ],
    ]))->toThrow(InvalidArgumentException::class, 'Duplicate category ID 10 at index 1.');

    test()->assertDatabaseCount('categories', 0);
});

it('rejects orders that reference missing coupons before writing any records', function () {
    $import = resolve(ImportLegacyBakeryData::class);

    expect(fn () => $import([
        'coupons' => [['id' => 25, 'code' => 'welcome5', 'type' => 'fixed_amount', 'value' => '5.00']],
        'orders' => [[
            'id' => 30,
            'order_number' => 'BOB-INVALID-COUPON',
            'customer_email' => 'jane@example.com',
            'coupon_id' => 999,
        ]],
    ]))->toThrow(InvalidArgumentException::class, 'Order at index 0 references missing coupon ID 999.');

    test()->assertDatabaseCount('coupons', 0)
        ->assertDatabaseCount('customers', 0)
        ->assertDatabaseCount('orders', 0);
});

it('rejects customer favorites that reference missing products before writing any records', function () {
    $import = resolve(ImportLegacyBakeryData::class);

    expect(fn () => $import([
        'customer_favorites' => [[
            'id' => 92,
            'customer_email' => 'jane@example.com',
            'product_id' => 999,
        ]],
    ]))->toThrow(InvalidArgumentException::class, 'Customer favorite at index 0 references missing product ID 999.');

    test()->assertDatabaseCount('customer_favorites', 0);
});

it('rejects reviews that reference missing products before writing any records', function () {
    $import = resolve(ImportLegacyBakeryData::class);

    expect(fn () => $import([
        'reviews' => [[
            'id' => 50,
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'rating' => 5,
            'body' => 'Excellent',
            'product_id' => 999,
        ]],
    ]))->toThrow(InvalidArgumentException::class, 'Review at index 0 references missing product ID 999.');

    test()->assertDatabaseCount('reviews', 0);
});

it('rejects recipes that reference missing products before writing any records', function () {
    $import = resolve(ImportLegacyBakeryData::class);

    expect(fn () => $import([
        'recipes' => [['id' => 60, 'product_id' => 999, 'name' => 'Sourdough Recipe']],
    ]))->toThrow(InvalidArgumentException::class, 'Recipe at index 0 references missing product ID 999.');

    test()->assertDatabaseCount('recipes', 0);
});

it('rejects waitlist entries that reference missing products before writing any records', function () {
    $import = resolve(ImportLegacyBakeryData::class);

    expect(fn () => $import([
        'waitlist_entries' => [[
            'id' => 91,
            'customer_name' => 'Jane Baker',
            'customer_email' => 'jane@example.com',
            'requested_date' => '2026-08-20',
            'product_id' => 999,
        ]],
    ]))->toThrow(InvalidArgumentException::class, 'Waitlist entry at index 0 references missing product ID 999.');

    test()->assertDatabaseCount('waitlist_entries', 0);
});

it('rejects unsupported enum values before writing any records', function () {
    $import = resolve(ImportLegacyBakeryData::class);

    expect(fn () => $import([
        'orders' => [[
            'id' => 30,
            'order_number' => 'BOB-INVALID',
            'customer_email' => 'jane@example.com',
            'status' => 'shipped',
        ]],
    ]))->toThrow(InvalidArgumentException::class, 'Unsupported order status [shipped].');

    test()->assertDatabaseCount('customers', 0)
        ->assertDatabaseCount('orders', 0);
});

it('imports Bakery on Biscotto assets into tenant-specific public storage', function () {
    Storage::fake('local');
    Storage::fake('public');
    $assetDirectory = Storage::disk('local')->path('legacy-public');
    mkdir("{$assetDirectory}/images", recursive: true);

    foreach (['logo.jpg', 'hero-banner.jpg', 'cassie-portrait.jpg', 'product-sourdough.jpg'] as $filename) {
        file_put_contents("{$assetDirectory}/images/{$filename}", "image-{$filename}");
    }

    $result = resolve(ImportLegacyBakeryAssets::class)([
        'products' => [['id' => 1, 'name' => 'Sourdough', 'image' => 'images/product-sourdough.jpg']],
        'settings' => [],
    ], $assetDirectory, 'bakery-on-biscotto');

    expect($result['store_logo'])->toBe('tenants/bakery-on-biscotto/bakery-on-biscotto/logo.jpg')
        ->and($result['data']['products'][0]['image'])->toBe('tenants/bakery-on-biscotto/bakery-on-biscotto/product-sourdough.jpg')
        ->and(collect($result['data']['settings'])->pluck('key'))->toContain('hero_image', 'store_photo', 'about_us_text', 'faq_items');

    Storage::disk('public')->assertExists([
        'tenants/bakery-on-biscotto/bakery-on-biscotto/logo.jpg',
        'tenants/bakery-on-biscotto/bakery-on-biscotto/hero-banner.jpg',
        'tenants/bakery-on-biscotto/bakery-on-biscotto/cassie-portrait.jpg',
        'tenants/bakery-on-biscotto/bakery-on-biscotto/product-sourdough.jpg',
    ]);
});
