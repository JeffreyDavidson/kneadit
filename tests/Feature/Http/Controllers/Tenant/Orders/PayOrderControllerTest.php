<?php

use App\Enums\Orders\PaymentMethod;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stripe\Checkout\Session;
use Stripe\Exception\InvalidRequestException;
use Tests\Support\Stripe\FakeCheckoutStripeClient;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
    config(['cashier.secret' => 'sk_test_fake_key']);
    app()->instance(TenantContract::class, new Tenant(['id' => 'test-bakery']));
    settings([
        'payment_methods' => json_encode(['stripe']),
        'stripe_connect_id' => 'acct_test',
        'stripe_connect_charges_enabled' => '1',
    ]);
});

test('an unpaid card order is sent back to its open Stripe session', function () {
    $order = Order::factory()->pending()->unpaid()->create([
        'payment_method' => PaymentMethod::Stripe,
        'stripe_checkout_session_id' => 'cs_pay_open',
        'total' => 50.00,
    ]);
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('retrieve')->returns(Session::constructFrom([
        'id' => 'cs_pay_open',
        'status' => 'open',
        'payment_status' => 'unpaid',
        'url' => 'https://checkout.stripe.test/c/pay/cs_pay_open',
    ]));

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->post(route('order.pay', $order, false));

    $response->assertRedirect('https://checkout.stripe.test/c/pay/cs_pay_open');
});

test('an unpaid card order whose session expired gets a new one', function () {
    $order = Order::factory()->pending()->unpaid()->create([
        'payment_method' => PaymentMethod::Stripe,
        'stripe_checkout_session_id' => 'cs_pay_old',
        'total' => 50.00,
    ]);
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('retrieve')->returns(Session::constructFrom([
        'id' => 'cs_pay_old',
        'status' => 'expired',
        'payment_status' => 'unpaid',
        'url' => null,
    ]));
    $sessions->expects('create')->returns(Session::constructFrom([
        'id' => 'cs_pay_new',
        'status' => 'open',
        'payment_status' => 'unpaid',
        'url' => 'https://checkout.stripe.test/c/pay/cs_pay_new',
    ]));

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->post(route('order.pay', $order, false));

    $response->assertRedirect('https://checkout.stripe.test/c/pay/cs_pay_new');
    expect($order->refresh()->stripe_checkout_session_id)->toBe('cs_pay_new');
});

test('the customer is told when a new Stripe session cannot be made', function () {
    $order = Order::factory()->pending()->unpaid()->create([
        'payment_method' => PaymentMethod::Stripe,
        'total' => 50.00,
    ]);
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('create')->throws(InvalidRequestException::factory('Stripe is unavailable.', 400, null, null));

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->post(route('order.pay', $order, false));

    $response->assertRedirect(route('order.confirmation', $order, false))
        ->assertSessionHas('warning');
});

dataset('orders that cannot be paid online', [
    'already paid' => fn () => Order::factory()->pending()->paid()->create(['payment_method' => PaymentMethod::Stripe, 'stripe_checkout_session_id' => 'cs_pay_x', 'total' => 50.00]),
    'cancelled' => fn () => Order::factory()->cancelled()->unpaid()->create(['payment_method' => PaymentMethod::Stripe, 'stripe_checkout_session_id' => 'cs_pay_x', 'total' => 50.00]),
    'paying by cash' => fn () => Order::factory()->pending()->unpaid()->create(['payment_method' => PaymentMethod::Cash, 'total' => 50.00]),
]);

test('an order that cannot be paid online never reaches Stripe', function (Order $order) {
    $sessions = FakeCheckoutStripeClient::bind();
    $sessions->expects('retrieve')->never();
    $sessions->expects('create')->never();

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->post(route('order.pay', $order, false));

    $response->assertRedirect(route('order.confirmation', $order, false));
})->with('orders that cannot be paid online');

test('only a customer who verified the order can pay for it', function () {
    $order = Order::factory()->pending()->unpaid()->create(['payment_method' => PaymentMethod::Stripe, 'total' => 50.00]);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.pay', $order, false));

    $response->assertRedirectContains('verify');
});
