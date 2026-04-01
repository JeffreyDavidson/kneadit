<?php

use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('can retrieve messages for an order', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson(route('order.messages', ['order' => $order], false));

    $response->assertOk()
        ->assertJsonIsArray('data');
});

test('can send a message on an order', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson(route('order.messages.send', ['order' => $order], false), [
            'sender_name' => 'Jane Customer',
            'sender_email' => 'jane@example.com',
            'message' => 'Can I change my order?',
        ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Message sent successfully.');

    expect($order->messages()->count())->toBe(1);
});
