<?php

use App\Models\Inventory\Product;
use App\Models\Inventory\ProductWaitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('can join product waitlist via json', function () {
    $product = Product::factory()->create();

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
    $product = Product::factory()->create();

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
