<?php

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\Order;
use App\Models\Staff\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('it marks an order as delivered', function () {
    $user = User::factory()->create();
    $order = Order::factory()->ready()->create();

    actingAs($user);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('driver.delivered', $order, false));

    $response->assertRedirect()
        ->assertSessionHas('success');

    expect($order->fresh()->status)->toBe(OrderStatus::Delivered);
});
