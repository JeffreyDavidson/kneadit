<?php

use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('stripe cancel sets payment status to unpaid and redirects', function () {
    $order = Order::factory()->create(['payment_status' => PaymentStatus::Paid]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.stripe.cancel', ['order' => $order], false));

    $response->assertRedirect();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Unpaid);
});
