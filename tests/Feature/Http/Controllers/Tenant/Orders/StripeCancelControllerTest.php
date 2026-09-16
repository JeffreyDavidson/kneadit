<?php

use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('stripe cancel preserves unpaid status and redirects', function () {
    $order = Order::factory()->create(['payment_status' => PaymentStatus::Unpaid]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.stripe.cancel', ['order' => $order], false));

    $response->assertRedirect();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Unpaid);
});

test('stripe cancel does not downgrade a paid order', function () {
    $order = Order::factory()->create(['payment_status' => PaymentStatus::Paid]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.stripe.cancel', ['order' => $order], false));

    $response->assertRedirect();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Paid);
});
