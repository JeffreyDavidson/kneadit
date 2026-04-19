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
