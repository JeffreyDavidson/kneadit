<?php

use App\Models\Inventory\Product;
use App\Models\Inventory\ProductWaitlist;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('can join product waitlist via json', function () {
    $product = Product::factory()->inactive()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('productWaitlist.join', [], false), [
            'product_id' => $product->id,
            'customer_email' => 'waitlist@example.com',
            'customer_name' => 'Jane Doe',
        ]);

    $response->assertOk()
        ->assertJsonStructure(['message']);

    expect(ProductWaitlist::query()->count())->toBe(1);
});

test('duplicate waitlist entry updates existing record', function () {
    $product = Product::factory()->inactive()->create();

    ProductWaitlist::factory()->create([
        'product_id' => $product->id,
        'customer_email' => 'repeat@example.com',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('productWaitlist.join', [], false), [
            'product_id' => $product->id,
            'customer_email' => 'repeat@example.com',
        ]);

    $response->assertOk();

    expect(ProductWaitlist::query()->count())->toBe(1);
});

test('joining the waitlist for an available product is rejected', function () {
    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('productWaitlist.join', [], false), [
            'product_id' => $product->id,
            'customer_email' => 'early@example.com',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['product_id' => 'This item is available now.']);

    expect(ProductWaitlist::query()->count())->toBe(0);
});
