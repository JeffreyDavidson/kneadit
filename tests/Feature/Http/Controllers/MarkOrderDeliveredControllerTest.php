<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it marks an order as delivered', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['status' => OrderStatus::Ready]);

    $response = $this
        ->withoutMiddleware(tenantMiddleware())
        ->actingAs($user)
        ->post(route('driver.delivered', $order));

    $response->assertRedirect()
        ->assertSessionHas('success');

    expect($order->fresh()->status)->toBe(OrderStatus::Delivered);
});
