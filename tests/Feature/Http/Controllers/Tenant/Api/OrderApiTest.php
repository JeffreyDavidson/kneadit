<?php

use App\Models\Inventory\Product;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('can create order via API and receive JSON:API envelope', function () {
    Mail::fake();
    $product = Product::factory()->active()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/orders', [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'customer_phone' => '555-0100',
            'delivery_date' => now()->addDays(3)->toDateString(),
            'delivery_type' => 'pickup',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

    $response->assertCreated()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonPath('data.type', 'orders')
        ->assertJsonStructure([
            'data' => ['id', 'type', 'attributes' => ['order_number', 'total', 'status']],
        ]);
});

test('API rejects an order with missing required fields with 422', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/orders', []);

    $response->assertStatus(422);

    $pointers = collect($response->json('errors'))->pluck('source.pointer')->all();
    expect($pointers)->toContain(
        '/data/attributes/customer_name',
        '/data/attributes/customer_email',
        '/data/attributes/items',
        '/data/attributes/delivery_date',
        '/data/attributes/delivery_type',
    );
});

test('API rejects an order with a non-existent product_id', function () {
    Mail::fake();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/orders', [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'customer_phone' => '555-0100',
            'delivery_date' => now()->addDays(3)->toDateString(),
            'delivery_type' => 'pickup',
            'items' => [
                ['product_id' => 999999, 'quantity' => 1],
            ],
        ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.0.source.pointer', '/data/attributes/items.0.product_id');
});
