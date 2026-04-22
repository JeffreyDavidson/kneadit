<?php

use App\Models\Customers\Customer;
use App\Models\Orders\Order;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('show renders the verification form', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.verify.show', ['order' => $order->order_number], false));

    $response->assertOk()
        ->assertViewIs('storefront.order-verify')
        ->assertViewHas('order')
        ->assertViewHas('settings');
});

test('store grants access and redirects when email matches', function () {
    $customer = Customer::factory()->create(['email' => 'jane@example.com']);
    $order = Order::factory()->for($customer)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.verify.store', ['order' => $order->order_number], false), [
            'email' => 'jane@example.com',
        ]);

    $response->assertRedirect(route('order.confirmation', ['order' => $order->order_number], false));
    expect(session('verified_order_numbers'))->toContain($order->order_number);
});

test('store ignores case + whitespace when comparing emails', function () {
    $customer = Customer::factory()->create(['email' => 'jane@example.com']);
    $order = Order::factory()->for($customer)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.verify.store', ['order' => $order->order_number], false), [
            'email' => '  JANE@Example.com  ',
        ]);

    $response->assertRedirect();
    expect(session('verified_order_numbers'))->toContain($order->order_number);
});

test('store rejects mismatched email and reports a validation error', function () {
    $customer = Customer::factory()->create(['email' => 'jane@example.com']);
    $order = Order::factory()->for($customer)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->from(route('order.verify.show', ['order' => $order->order_number], false))
        ->post(route('order.verify.store', ['order' => $order->order_number], false), [
            'email' => 'attacker@example.com',
        ]);

    $response->assertRedirect(route('order.verify.show', ['order' => $order->order_number], false))
        ->assertSessionHasErrors('email');

    expect(session('verified_order_numbers', []))->not->toContain($order->order_number);
});

test('protected route redirects to verify when session has no grant', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertRedirect(route('order.verify.show', ['order' => $order->order_number], false));
});

test('protected JSON route returns 403 when session has no grant', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson(route('order.reorder', ['order' => $order->order_number], false));

    $response->assertForbidden();
});
