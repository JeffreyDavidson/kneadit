<?php

use App\Models\Orders\Order;
use App\Services\Stripe\StripeCheckoutService;
use JMac\Testing\Double;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('redirects to order confirmation with success message', function () {
    $order = Order::factory()->create();

    $stripeService = Double::for(StripeCheckoutService::class);
    $stripeService->expects('handleCheckoutComplete')->with('cs_test_123')->returns($order);
    app()->instance(StripeCheckoutService::class, $stripeService);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.stripe.success', ['order' => $order, 'session_id' => 'cs_test_123'], false));

    $response->assertRedirect()
        ->assertSessionHas('success', 'Payment successful! Your order has been placed.');
});

test('calls handleCheckoutComplete when session_id is present', function () {
    $order = Order::factory()->create();

    $stripeService = Double::for(StripeCheckoutService::class);
    $stripeService->expects('handleCheckoutComplete')
        ->with('cs_test_123')
        ->returns($order);
    app()->instance(StripeCheckoutService::class, $stripeService);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.stripe.success', ['order' => $order, 'session_id' => 'cs_test_123'], false));

    $response->assertRedirect()
        ->assertSessionHas('success', 'Payment successful! Your order has been placed.');
});

test('rejects a missing session id', function () {
    $order = Order::factory()->create();

    $stripeService = Double::for(StripeCheckoutService::class);
    $stripeService->expects('handleCheckoutComplete')->never();
    app()->instance(StripeCheckoutService::class, $stripeService);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.stripe.success', $order, false));

    $response->assertForbidden();
});

test('rejects an unverified or mismatched checkout session', function () {
    $order = Order::factory()->create();
    $otherOrder = Order::factory()->create();

    $stripeService = Double::for(StripeCheckoutService::class);
    $stripeService->expects('handleCheckoutComplete')
        ->with('cs_test_123')
        ->returns($otherOrder);
    app()->instance(StripeCheckoutService::class, $stripeService);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.stripe.success', ['order' => $order, 'session_id' => 'cs_test_123'], false));

    $response->assertForbidden();
});
