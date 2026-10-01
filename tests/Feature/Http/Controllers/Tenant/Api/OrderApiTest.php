<?php

use App\Actions\Orders\CreateOrder;
use App\Enums\Orders\DeliveryType;
use App\Exceptions\Orders\PickupSlotUnavailableException;
use App\Models\Inventory\Product;
use App\Models\Operations\BusinessSchedule;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\Mail;
use JMac\Testing\Double;

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

/**
 * Turns pickup slots on (08:00-10:00, 30 minutes, 2 per slot) for the weekday three days out.
 *
 * @return array<string, mixed> the order payload for a pickup on that day
 */
function apiPickupSlotPayload(Product $product, ?string $time): array
{
    settings([
        'pickup_slots_enabled' => true,
        'pickup_slot_interval_minutes' => 30,
        'pickup_slot_max_per_window' => 2,
    ]);
    $date = now()->addDays(3);
    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => true,
        'open_time' => '08:00',
        'close_time' => '10:00',
    ]);

    return [
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@example.com',
        'delivery_date' => $date->toDateString(),
        'delivery_time' => $time,
        'delivery_type' => 'pickup',
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ];
}

test('API pickup orders are rejected for a full slot, a time outside opening hours, or a missing time', function (?string $time) {
    Mail::fake();
    $payload = apiPickupSlotPayload(Product::factory()->active()->create(), $time);
    Order::factory()->confirmed()->count(2)->create([
        'delivery_date' => $payload['delivery_date'],
        'delivery_time' => '08:30',
        'delivery_type' => DeliveryType::Pickup->value,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/orders', $payload);

    $response->assertStatus(422)
        ->assertJsonPath('errors.0.source.pointer', '/data/attributes/delivery_time');
    expect(Order::query()->count())->toBe(2);
})->with([
    'a full slot' => ['08:30'],
    'a time outside opening hours' => ['23:00'],
    'a missing time' => [null],
]);

test('API pickup orders for an open slot are created', function () {
    Mail::fake();
    $payload = apiPickupSlotPayload(Product::factory()->active()->create(), '09:00');

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/orders', $payload);

    $response->assertCreated();
});

test('API pickup orders for a slot that fills while the order is placed are rejected', function () {
    $createOrder = Double::for(CreateOrder::class);
    $createOrder->expects('__invoke')->throws(new PickupSlotUnavailableException('2026-05-04', '08:30'));
    app()->instance(CreateOrder::class, $createOrder);
    $payload = apiPickupSlotPayload(Product::factory()->active()->create(), '08:30');

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/orders', $payload);

    $response->assertStatus(422)
        ->assertJsonPath('errors.0.source.pointer', '/data/attributes/delivery_time');
});

test('API orders whose products are all inactive are rejected as unavailable items', function () {
    $product = Product::factory()->inactive()->create();
    $payload = apiPickupSlotPayload($product, '09:00');

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/orders', $payload);

    $response->assertStatus(422)
        ->assertJsonPath('errors.0.source.pointer', '/data/attributes/items')
        ->assertJsonPath('errors.0.detail', 'Some items in your cart are no longer available. Please review your cart.');
    expect(Order::query()->count())->toBe(0);
});
