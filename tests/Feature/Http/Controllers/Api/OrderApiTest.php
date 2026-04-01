<?php

use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('can create order via API', function () {
    Mail::fake();
    $product = Product::factory()->create(['is_active' => true]);

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
        ->assertJsonStructure(['data' => ['order_number', 'total'], 'message']);
});
